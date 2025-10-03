<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class WorkStreamExporter extends Controller
{
    public function exportCsv(Request $request)
    {
        // Read & normalize query params
        $start  = trim((string) $request->query('start_date', ''));
        $end    = trim((string) $request->query('end_date', ''));
        $status = trim((string) $request->query('status', ''));
        $posStr = trim((string) $request->query('position_title', ''));

        // Build query with optional filters
        $query = Applicant::query();

        // Date range filter: only apply if BOTH start & end are provided (non-empty)
        if ($start !== '' && $end !== '') {
            // Expecting YYYY-MM-DD inputs; if your inputs include time, adjust as needed
            $query->whereBetween('application_date', [$start, $end]);
        }

        // Status filter: exact match if provided
        if ($status !== '') {
            $query->where('status', $status);
        }

        // Position title filter: comma-separated list -> whereIn
        if ($posStr !== '') {
            $positions = array_values(array_filter(array_map(
                fn ($p) => trim($p),
                explode(',', $posStr)
            ), fn ($p) => $p !== ''));

            if (!empty($positions)) {
                $query->whereIn('position_title', $positions);
            }
        }

        // Columns to export (aligned with your model fillable/casts)
        $columns = [
            'uuid',
            'first_name',
            'last_name',
            'email',
            'phone',
            'name',
            'status',
            'current_stage',
            'application_date',
            'hired_at',
            'sms_phone_number',
            'global_phone_number',
            'language',
            'referer_source',
            'position_title',
            'location_name',
        ];

        $fileName = 'applicants_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Cache-Control'       => 'no-store, no-cache',
        ];

        $callback = function () use ($query, $columns) {
            $out = fopen('php://output', 'w');

            // (Optional) Write UTF-8 BOM for Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");

            // Header row
            fputcsv($out, $columns);

            // Stream results to avoid memory issues
            foreach ($query->orderBy('application_date')->cursor() as $applicant) {
                $row = [];
                foreach ($columns as $col) {
                    $value = $applicant->$col;

                    // Format dates as YYYY-MM-DD
                    if (in_array($col, ['application_date', 'hired_at'], true)) {
                        $value = $value ? (string) optional($applicant->$col)->format('Y-m-d') : null;
                    }

                    // Ensure scalar string output
                    if (is_bool($value)) {
                        $value = $value ? '1' : '0';
                    } elseif (is_array($value) || is_object($value)) {
                        $value = (string) json_encode($value, JSON_UNESCAPED_UNICODE);
                    }

                    $row[] = $value;
                }
                fputcsv($out, $row);
            }

            fclose($out);
        };

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }
}
