<?php

namespace App\Support;

/**
 * Where each street name sits on the city images (Daniel's street plan,
 * 2026-10-22). Keys are 'overview' (the top view) or the district number
 * (its 3D render). Each row: [street name as in the `streets` table,
 * x %, y % of the image (label centre), angle in degrees].
 *
 * The top view positions are traced from his plan. The 3D renders are
 * parallel projections of the same model, so the positions there come from
 * fitting each render to the plan on a few road junctions and putting each
 * name on the longest visible stretch of its road. Short or hidden stretches
 * are left out (e.g. Marx/Vitoria Street on render 2) - they still show on
 * the top view.
 *
 * Daniel, 2026-10-24: some names sat on buildings, kerbs or the white dashes
 * and were hard to read. Every 3D position was re-checked against the render's
 * own pixels and moved along its street to the nearest spot where the whole
 * name lies on plain grey asphalt (a lane, clear of the dashes).
 *
 * Later the same day: each 3D angle is now measured from the road's own kerbs
 * and lane dashes right where the name sits (the renders are not perfectly
 * parallel, so one angle per district left some names slanting across their
 * lane), and the roads that had no name yet got one - Fisher Street (3),
 * Veblen Road and Engels Avenue (4), Say Boulevard, Gramsci Street, Walras Way,
 * Bogle Boulevard, Mises Memorial Drive and Kahneman Court (8). Where trees
 * hide part of a road, the label's asphalt-grey backing keeps it readable.
 */
class CityStreetLabels
{
    public const LABELS = [
        'overview' => [
            ['Buffett Boulevard', 37.35, 18.94, 0],
            ['Keynes Boulevard', 37.61, 30.5, 0],
            ['Marx Street', 27.77, 26.18, -90],
            ['Vitoria Street', 47.52, 25.42, -90],
            ['Molina Lane', 8.35, 34.6, -90],
            ['Marshall Street', 19.84, 39.57, -90],
            ['Samuelson Street', 37.61, 41.08, -90],
            ['Piketty Path', 56.04, 38.6, -90],
            ['Galbraith Grove', 66.08, 37.73, 0],
            ['Fisher Street', 67.53, 28.12, -90],
            ['Sowell Street', 75.92, 31.04, -90],
            ['Engels Avenue', 82.85, 34.28, -90],
            ['Templeton Terrace', 87.67, 37.73, 0],
            ['Adam Smith Avenue', 49.04, 48.86, 0],
            ['Lynch Drive', 52.14, 58.58, -90],
            ['Friedman Drive', 58.61, 63.44, 0],
            ['Pareto Path', 24.34, 61.2, 0],
            ['Hayek Street', 16.81, 63.98, -90],
            ['Gramsci Street', 33.19, 70.46, -90],
            ['Walras Way', 40.05, 72.62, -90],
            ['Schumpeter Street', 49.7, 70.68, 0],
            ['Say Boulevard', 23.28, 79.43, 0],
            ['Bogle Boulevard', 64.89, 66.14, -90],
            ['Mises Memorial Drive', 73.14, 63.12, -90],
            ['Veblen Road', 82.72, 65.06, 0],
            ['Graham Lane', 92.1, 67.76, -90],
            ['Kahneman Court', 80.74, 77.27, 0],
        ],
        1 => [
            ['Buffett Boulevard', 66.14, 49.39, 38.4],
            ['Keynes Boulevard', 57.84, 72.77, 38.1],
            ['Adam Smith Avenue', 29.31, 88.65, 38.1],
            ['Molina Lane', 32.21, 52.37, -24.7],
            ['Marshall Street', 46.97, 82.73, -24.7],
        ],
        2 => [
            ['Buffett Boulevard', 26.29, 34.62, 29.9],
            ['Keynes Boulevard', 33.67, 65.57, 29.9],
        ],
        3 => [
            ['Buffett Boulevard', 39.16, 29.73, 29.7],
            ['Galbraith Grove', 21.14, 43.11, 29.9],
            ['Adam Smith Avenue', 29.23, 67.57, 29.9],
            ['Sowell Street', 43.66, 56.71, -29.8],
            ['Engels Avenue', 62.15, 57.54, -30.2],
            ['Graham Lane', 53.5, 89.39, -30],
            ['Fisher Street', 42.22, 36.46, -30],
        ],
        4 => [
            ['Adam Smith Avenue', 49.55, 17.21, 30.2],
            ['Kahneman Court', 33.31, 67.31, 30],
            ['Graham Lane', 71.22, 68.9, -30.1],
            ['Bogle Boulevard', 8.31, 31.22, -30.1],
            ['Mises Memorial Drive', 40.57, 30.26, -29.9],
            ['Veblen Road', 40.9, 45.6, 29.8],
            ['Engels Avenue', 58.89, 47.52, -30],
        ],
        5 => [
            ['Keynes Boulevard', 39.12, 18.69, 30.1],
            ['Adam Smith Avenue', 39.05, 59.21, 30.1],
            ['Marshall Street', 14.72, 16.73, -30],
            ['Samuelson Street', 47.63, 52.91, -29.8],
            ['Piketty Path', 76.64, 94.89, -29.8],
        ],
        6 => [
            ['Keynes Boulevard', 92.02, 58.24, 30.1],
            ['Adam Smith Avenue', 46.81, 77.73, 30.1],
            ['Molina Lane', 43.88, 46.29, -30.1],
            ['Marshall Street', 78.32, 77.23, -29.9],
        ],
        7 => [
            ['Say Boulevard', 46.04, 64.14, 30.1],
            ['Molina Lane', 54.5, 39.87, -30],
            ['Hayek Street', 76.22, 55.32, -30.2],
        ],
        8 => [
            ['Pareto Path', 31.84, 16.83, 32.1],
            ['Friedman Drive', 68.27, 50.43, 29.9],
            ['Schumpeter Street', 60.93, 59.19, 30.1],
            ['Hayek Street', 14.22, 21.59, -29.9],
            ['Lynch Drive', 57.05, 51.7, -29.9],
            ['Say Boulevard', 27.35, 38.33, 30],
            ['Gramsci Street', 32.89, 36.84, -30],
            ['Walras Way', 39.84, 43.79, -26.5],
            ['Bogle Boulevard', 66.64, 67.64, -30],
            ['Mises Memorial Drive', 86.07, 66.38, -30],
            ['Kahneman Court', 72.81, 79.09, 30],
        ],
        9 => [
            ['Kahneman Court', 63.78, 43.23, 30.1],
        ],
    ];

    /** @return array<int, array{0: string, 1: float, 2: float, 3: float}> */
    public static function for(string|int $view): array
    {
        return self::LABELS[$view] ?? [];
    }
}
