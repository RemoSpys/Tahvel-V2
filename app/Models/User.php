<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tahvel_cookie',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tahvelUser(): mixed
    {
        $response = Http::withHeaders([
            'cookie' => $this->tahvel_cookie,
        ])->get('https://tahvel.edu.ee/hois_back/user');

        if (!$response->ok() || empty($response->body())) {
            return null;
        }

        return $response->json();
    }

    public function tahvelJournals(): mixed
    {
        // Get user data first to extract student ID dynamically
        $userData = $this->tahvelUser();

        if (!$userData || !isset($userData['student'])) {
            return null;
        }

        $studentId = $userData['student'];

        $response = Http::withHeaders([
            'cookie' => $this->tahvel_cookie,
        ])->get('https://tahvel.edu.ee/hois_back/journals/studentJournalTasks', [
            'presentTasks' => false,
            'studentId' => $studentId,
        ]);

        if (!$response->ok() || empty($response->body())) {
            return null;
        }

        return $response->json();
    }

    /**
     * Get timetable data for the user
     */
    public function tahvelTimetable($from = null, $thru = null): mixed
    {
        if (!$this->tahvel_cookie) {
            return null;
        }

        // Get user data to extract student ID and school ID
        $userData = $this->tahvelUser();
        if (!$userData || !isset($userData['student'])) {
            return null;
        }

        $studentId = $userData['student'];
        $schoolId = $userData['school']['id'] ?? 38; // Fallback to your school ID

        // Calculate date range if not provided (current week)
        if (!$from || !$thru) {
            $startOfWeek = Carbon::now()->startOfWeek();
            $from = $from ?: $startOfWeek->toISOString();
            $thru = $thru ?: $startOfWeek->copy()->endOfWeek()->toISOString();
        }

        try {
            $response = Http::withHeaders([
                'Cookie' => $this->tahvel_cookie,
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ])->get("https://tahvel.edu.ee/hois_back/timetableevents/timetableByStudent/{$schoolId}", [
                'student' => $studentId,
                'from' => $from,
                'thru' => $thru
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            \Log::warning('Tahvel timetable API returned non-successful status', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            \Log::error('Tahvel timetable fetch failed: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'school_id' => $schoolId,
                'from' => $from,
                'thru' => $thru
            ]);
            return null;
        }
    }
}