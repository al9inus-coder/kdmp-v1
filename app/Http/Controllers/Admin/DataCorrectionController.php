<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataCorrection;
use App\Support\DataCorrectionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DataCorrectionController extends Controller
{
    /** Halaman 1 — Dashboard Data Correction Center (search-first, gaya Google). */
    public function index()
    {
        return view('admin.data-corrections.index', [
            'types' => DataCorrectionRegistry::types(),
        ]);
    }

    /** Live search lintas objek bisnis (JSON, dipakai kolom pencarian dashboard). */
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        foreach (DataCorrectionRegistry::types() as $key => $def) {
            $q = $def['model']::query()->with($def['with']);
            ($def['search'])($q, $term);
            $objects = $q->latest('updated_at')->limit(6)->get();

            if ($objects->isEmpty()) {
                continue;
            }

            $counts = DataCorrection::where('object_type', $key)
                ->whereIn('object_id', $objects->pluck($objects->first()->getKeyName()))
                ->selectRaw('object_id, count(*) as total')
                ->groupBy('object_id')
                ->pluck('total', 'object_id');

            foreach ($objects as $object) {
                $results[] = [
                    'type'        => $key,
                    'label'       => $def['label'],
                    'icon'        => $def['icon'],
                    'iconBg'      => $def['iconBg'],
                    'chip'        => $def['chip'],
                    'title'       => ($def['title'])($object),
                    'subtitle'    => ($def['subtitle'])($object),
                    'status'      => ($def['statusLabel'])($object),
                    'corrections' => (int) ($counts[$object->getKey()] ?? 0),
                    'editUrl'     => route('admin.data-corrections.edit', [$key, $object->getKey()]),
                    'historyUrl'  => route('admin.data-corrections.history', [$key, $object->getKey()]),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    /** Halaman 2 — Form Koreksi Data (dialog konfirmasi ada di halaman ini). */
    public function edit(string $type, int $id)
    {
        $def    = DataCorrectionRegistry::type($type);
        $object = DataCorrectionRegistry::resolveObject($type, $id);

        $fields = [];
        foreach ($def['fields'] as $key => $field) {
            $target = DataCorrectionRegistry::resolveTarget($object, $field);
            if (! $target) {
                continue; // relasi belum ada — field tidak tersedia untuk objek ini
            }
            $old = DataCorrectionRegistry::currentValue($target, $field);
            $fields[$key] = [
                'label'      => $field['label'],
                'type'       => $field['type'] ?? 'text',
                'old'        => $old ?? '',
                'oldDisplay' => DataCorrectionRegistry::displayValue($old, $field),
            ];
        }

        $lastCorrection = DataCorrection::with('user')
            ->where('object_type', $type)
            ->where('object_id', $object->getKey())
            ->latest()
            ->first();

        $historyCount = DataCorrection::where('object_type', $type)
            ->where('object_id', $object->getKey())
            ->count();

        return view('admin.data-corrections.edit', [
            'type'           => $type,
            'def'            => $def,
            'object'         => $object,
            'fields'         => $fields,
            'title'          => ($def['title'])($object),
            'subtitle'       => ($def['subtitle'])($object),
            'statusLabel'    => ($def['statusLabel'])($object),
            'approval'       => ($def['approval'])($object),
            'lastCorrection' => $lastCorrection,
            'historyCount'   => $historyCount,
        ]);
    }

    /** Simpan koreksi: update field + catat riwayat secara atomik. */
    public function update(Request $request, string $type, int $id)
    {
        $def    = DataCorrectionRegistry::type($type);
        $object = DataCorrectionRegistry::resolveObject($type, $id);

        $data = $request->validate([
            'field_key'    => ['required', Rule::in(array_keys($def['fields']))],
            'new_value'    => ['required', 'string', 'max:2000'],
            'expected_old' => ['nullable', 'string'],
            'reason'       => ['required', 'string', 'min:20', 'max:1000'],
            'attachment'   => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [], [
            'field_key'  => 'field',
            'new_value'  => 'nilai baru',
            'reason'     => 'alasan koreksi',
            'attachment' => 'lampiran',
        ]);

        $field = $def['fields'][$data['field_key']];

        $newValue = trim($data['new_value']);
        if (($field['type'] ?? 'text') === 'date') {
            $request->validate(['new_value' => ['date']], [], ['new_value' => 'nilai baru']);
            $newValue = \Illuminate\Support\Carbon::parse($newValue)->format('Y-m-d');
        }

        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('data-corrections', 'local')
            : null;

        DB::transaction(function () use ($request, $type, $object, $data, $field, $newValue, $attachmentPath) {
            $target = DataCorrectionRegistry::resolveTarget($object, $field);

            if (! $target) {
                throw ValidationException::withMessages([
                    'field_key' => 'Field ini tidak tersedia untuk objek yang dipilih.',
                ]);
            }

            // Kunci baris & baca ulang nilai lama dari server (bukan dari client).
            $target = $target->newQuery()->lockForUpdate()->findOrFail($target->getKey());
            $current = DataCorrectionRegistry::currentValue($target, $field);

            // Optimistic lock: nilai berubah sejak halaman dibuka.
            if (($data['expected_old'] ?? '') !== ($current ?? '')) {
                throw ValidationException::withMessages([
                    'new_value' => 'Nilai lama sudah berubah sejak halaman dibuka (kemungkinan dikoreksi user lain). Muat ulang halaman dan periksa kembali.',
                ]);
            }

            if ($newValue === ($current ?? '')) {
                throw ValidationException::withMessages([
                    'new_value' => 'Nilai baru identik dengan nilai lama — tidak ada yang perlu dikoreksi.',
                ]);
            }

            $target->{$field['column']} = $newValue;
            $target->save();

            DataCorrection::create([
                'object_type'     => $type,
                'object_id'       => $object->getKey(),
                'target_type'     => get_class($target),
                'target_id'       => $target->getKey(),
                'field_key'       => $data['field_key'],
                'field_label'     => $field['label'],
                'old_value'       => $current,
                'new_value'       => $newValue,
                'reason'          => trim($data['reason']),
                'attachment_path' => $attachmentPath,
                'user_id'         => $request->user()->id,
                'ip_address'      => $request->ip(),
                'user_agent'      => Str::limit((string) $request->userAgent(), 250, ''),
            ]);
        });

        return redirect()
            ->route('admin.data-corrections.edit', [$type, $object->getKey()])
            ->with('success', 'Koreksi "' . $field['label'] . '" tersimpan dan tercatat di riwayat.');
    }

    /** Halaman 4 — Riwayat Koreksi (timeline). */
    public function history(string $type, int $id)
    {
        $def    = DataCorrectionRegistry::type($type);
        $object = DataCorrectionRegistry::resolveObject($type, $id);

        $corrections = DataCorrection::with('user')
            ->where('object_type', $type)
            ->where('object_id', $object->getKey())
            ->latest()
            ->paginate(15);

        // Format nilai lama/baru sesuai tipe field (tanggal → format Indonesia).
        $corrections->getCollection()->transform(function (DataCorrection $c) use ($def) {
            $field = $def['fields'][$c->field_key] ?? ['type' => 'text'];
            $c->old_display = DataCorrectionRegistry::displayValue($c->old_value, $field);
            $c->new_display = DataCorrectionRegistry::displayValue($c->new_value, $field);

            return $c;
        });

        return view('admin.data-corrections.history', [
            'type'        => $type,
            'def'         => $def,
            'object'      => $object,
            'title'       => ($def['title'])($object),
            'subtitle'    => ($def['subtitle'])($object),
            'statusLabel' => ($def['statusLabel'])($object),
            'corrections' => $corrections,
        ]);
    }

    /** Unduh lampiran koreksi dari private storage setelah melewati middleware admin. */
    public function downloadAttachment(string $type, int $id, DataCorrection $correction)
    {
        abort_unless(
            $correction->object_type === $type
                && (int) $correction->object_id === $id
                && filled($correction->attachment_path),
            404
        );

        abort_unless(Storage::disk('local')->exists($correction->attachment_path), 404);

        return Storage::disk('local')->download($correction->attachment_path);
    }
}
