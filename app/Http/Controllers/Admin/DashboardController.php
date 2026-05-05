<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardRequest;
use App\Models\Movie;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(DashboardRequest $request)
    {
        $startDate = $request->periodStartDate();

        $revenueQuery = Order::paid();
        if ($startDate) {
            $revenueQuery->where('created_at', '>=', $startDate);
        }
        $totalRevenue = $revenueQuery->sum('total_amount');

        $ticketQuery = Ticket::valid();
        if ($startDate) {
            $revenueQuery->where('created_at', '>=', $startDate);
        }
        $totalTickets = $ticketQuery->count();

        $orderQuery = Order::paid();
        if ($startDate) {
            $orderQuery->where('created_at', '>=', $startDate);
        }
        $totalOrders = $orderQuery->count();

        $totalCustomers = User::customers()->count();

        return response()->json([
            'data' => [
                'total_revenue' => (float) $totalRevenue,
                'total_tickets' => $totalTickets,
                'total_orders' => $totalOrders,
                'total_customers' => $totalCustomers,
                'period' => $request->input('period', 'total')
            ]
        ]);
    }

    public function topMovies(DashboardRequest $request)
    {
        $startDate = $request->periodStartDate();
        $limit = $request->input('limit', 10);

        $query = Movie::select('movies.id', 'movies.title', 'movies.slug', 'movies.image_url')
            ->join('screenings, movies.id = screenings.movie_id')
            ->join('tickets, screenings.id = tickets.screening_id')
            ->whereIn('tickets.status', ['active', 'used']);

        if ($startDate) {
            $query->where('tickets.created_at', '>=', $startDate);
        }

        $movies = $query
            ->groupBy('movies.id', 'movies.title', 'movies.slug', 'movies.image_url')
            ->selectRaw('COUNT(tickets.id) as tickets_sold')
            ->selectRaw('SUM(tickets.unit_price) as total_revenue')
            ->orderByDesc('tickets_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $movies,
            'period' => $request->input('period', 'total')
        ]);
    }

    public function revenue(DashboardRequest $request)
    {
        $startDate = $request->periodStartDate();

        $query = Order::paid()
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(*), as orders_count')
            )
            ->groupBy('date')
            ->orderBy('date');

        if ($startDate) {
            $query->where('paid_at', '>=', $startDate);
        }

        $revenue = $query->get();

        return response()->json([
            'data' => $revenue,
            'period' => $request->input('period', 'total')
        ]);
    }

    public function popularTimes(DashboardRequest $request)
    {
        $startDate = $request->periodStartDate();

        $query = Ticket::valid()
            ->join('screenings', 'tickets.screening_id', '=', 'screenings.id')
            ->select(
                DB::raw('HOUR(screenings.start_time) as hour'),
                DB::raw('COUNT(tickets.id) as tickets_sold')
            )
            ->groupBy('hour')
            ->orderByDesc('tickets_sold');

            if($startDate){
                $query->where('tickets.created_at', '>=', $startDate);
            }

            $times = $query->get();

            return response()->json([
                'data' => $times,
                'period' => $request->input('period', 'total')
            ]);
    }

    public function topGenres(DashboardRequest $request)
    {
        $startDate = $request->periodStartDate();

        $query = DB::table('genres')
        ->join('genre_movie', 'genres.id', '=', 'genre_movie.genre_id')
        ->join('movies', 'genre_movie.movie_id', '=', 'movies.id')
        ->join('screenings', 'movies.id', '=', 'screenings.movie_id')
        ->join('tickets', 'screenings.id', '=', 'tickets.screening_id')
        ->whereIn('tickets.status', ['active', 'used'])
        ->select(
            'genres.id',
            'genres.name',
            'genres.slug',
            DB::raw('COUNT(tickets.id) as tickets_sold')
        )
        ->groupBy('genres.id', 'genres.name', 'genres.slug')
        ->orderByDesc('tickets_sold');

        if($startDate){
            $query->where('tickets.created_at', '>=', $startDate);
        }

        $genres = $query->limit($request->input('limit', 10))->get();

        return response()->json([
            'data' => $genres,
            'period' => $request->input('period', 'total')
        ]);
    }
}
