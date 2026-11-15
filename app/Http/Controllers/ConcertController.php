<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ConcertController extends Controller
{
    /**
     * Halaman 1: Katalog Konser
     * Mendukung pencarian, filter genre, kota, rentang waktu, serta eager loading ticketTiers.
     */
    public function index(Request $request): View
    {
        $query = Concert::query()->with('ticketTiers');

        // Pencarian berbasis query string (?search=nama_artis_atau_konser)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('headliner', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter kategori genre musik
        if ($genre = $request->input('genre')) {
            $query->where('genre', $genre);
        }

        // Filter kota penyelenggaraan
        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        // Filter rentang waktu pelaksanaan konser
        if ($timeRange = $request->input('time_range')) {
            match ($timeRange) {
                'this_week', 'minggu_ini' => $query->whereBetween('event_date', [
                    Carbon::now()->startOfWeek()->toDateString(),
                    Carbon::now()->endOfWeek()->toDateString(),
                ]),
                'this_month', 'bulan_ini' => $query->whereBetween('event_date', [
                    Carbon::now()->startOfMonth()->toDateString(),
                    Carbon::now()->endOfMonth()->toDateString(),
                ]),
                'upcoming', 'mendatang' => $query->where('event_date', '>=', Carbon::now()->toDateString()),
                default => null,
            };
        }

        $concerts = $query->orderBy('event_date', 'asc')
            ->paginate(12)
            ->withQueryString();

        $genres = ['Pop', 'Rock', 'Indie', 'EDM', 'Jazz', 'K-Pop'];
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Bali', 'Balikpapan'];

        return view('concerts.index', [
            'concerts' => $concerts,
            'genres' => $genres,
            'cities' => $cities,
            'selectedGenre' => $genre,
            'selectedCity' => $city,
            'selectedTimeRange' => $timeRange,
            'search' => $search,
        ]);
    }

    /**
     * Halaman 2: Rincian Konser
     * Menampilkan informasi konser, daftar tiers lengkap, stage map, dan regulasi acara.
     */
    public function show(Concert $concert): View
    {
        $concert->load('ticketTiers');

        return view('concerts.show', [
            'concert' => $concert,
        ]);
    }
}
