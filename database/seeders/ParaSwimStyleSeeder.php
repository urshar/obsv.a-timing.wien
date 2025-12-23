<?php

namespace Database\Seeders;

use App\Models\ParaSwimStyle;
use Illuminate\Database\Seeder;

class ParaSwimStyleSeeder extends Seeder
{
    public function run(): void
    {
        $strokes = [
            // LENEX => [en, de, abbr]
            'FR' => ['Freestyle', 'Freistil', 'Fr'],
            'BK' => ['Backstroke', 'Rücken', 'Bk'],
            'BR' => ['Breaststroke', 'Brust', 'Br'],
            'FL' => ['Butterfly', 'Schmetterling', 'Fl'],
            'IM' => ['Medley', 'Lagen', 'IM'],
        ];

        // Distanzen (Einzel)
        $individualDistances = [25, 50, 100, 200, 400, 800, 1500];

        // Staffeln: nur FR und IM; keine 4x200 IM
        $relayCount = 4;
        $relayStrokes = ['FR', 'IM'];
        $relayDistances = [25, 50, 100, 200];

        $rows = [];
        $now = now();

        /**
         * Einzelbewerbe – nach Distanzregeln filtern:
         * - 400m nur FR und IM
         * - 800m, 1500m nur FR
         * - IM (Lagen) gibt es NICHT für 25m und 50m
         * - Sonst (25, 50, 100, 200) alle Strokes, außer obige IM-Regel
         */
        foreach ($individualDistances as $distance) {
            $allowedStrokes = match ($distance) {
                400 => ['FR', 'IM'],
                800, 1500 => ['FR'],
                default => array_keys($strokes),
            };

            foreach ($allowedStrokes as $strokeCode) {
                // IM-Regel: keine 25m/50m Lagen
                if ($strokeCode === 'IM' && in_array($distance, [25, 50], true)) {
                    continue;
                }

                [$en, $de, $abbr] = $strokes[$strokeCode];

                $rows[] = [
                    'key' => ParaSwimStyle::makeKey($distance, $strokeCode),
                    'relay_count' => null,
                    'distance' => $distance,
                    'stroke' => $strokeCode,
                    'stroke_name_en' => $en,
                    'stroke_name_de' => $de,
                    'abbreviation' => $abbr,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        /**
         * Spezialfall:
         * 150m IM (Lagen) – fachlich nur für Sportklasse S5 und kleiner zulässig.
         * Das wird hier angelegt, die Zulässigkeit kommt später in die Business-Logik.
         */
        [$en, $de, $abbr] = $strokes['IM'];
        $rows[] = [
            'key' => ParaSwimStyle::makeKey(150, 'IM'),
            'relay_count' => null,
            'distance' => 150,
            'stroke' => 'IM',
            'stroke_name_en' => $en,
            'stroke_name_de' => $de,
            'abbreviation' => $abbr,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Staffeln (nur FR/IM, aber keine 4x200 IM)
        foreach ($relayDistances as $distance) {
            foreach ($relayStrokes as $strokeCode) {
                if ($distance === 200 && $strokeCode === 'IM') {
                    continue;
                }

                [$en, $de, $abbr] = $strokes[$strokeCode];

                $rows[] = [
                    'key' => ParaSwimStyle::makeKey($distance, $strokeCode, $relayCount),
                    'relay_count' => $relayCount,
                    'distance' => $distance,
                    'stroke' => $strokeCode,
                    'stroke_name_en' => $en,
                    'stroke_name_de' => $de,
                    'abbreviation' => $abbr,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        ParaSwimStyle::query()->upsert(
            $rows,
            ['key'],
            [
                'relay_count',
                'distance',
                'stroke',
                'stroke_name_en',
                'stroke_name_de',
                'abbreviation',
                'updated_at',
            ]
        );
    }
}
