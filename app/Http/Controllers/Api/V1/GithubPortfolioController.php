<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GithubPortfolioController extends Controller
{
    /**
     * Laravel bertindak sebagai SERVICE CONSUMER yang memanggil GitHub REST API
     * Endpoint: GET /api/v1/portfolios/github/{username}
     */
    public function show(string $username): JsonResponse
    {
        // 1. Siapkan HTTP Client dengan Header kontrak resmi & batas waktu 10 detik
        $client = Http::accept('application/vnd.github+json')
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->timeout(10);

        // Opsional: Jika GITHUB_TOKEN diisi di .env, lampirkan sebagai Bearer Token
        if ($token = env('GITHUB_TOKEN')) {
            $client = $client->withToken($token);
        }

        // 2. Panggil endpoint profil & repositori dari GitHub API
        $userRes = $client->get("https://api.github.com/users/{$username}");

        // 3. Tangani jika user tidak ditemukan (404) atau GitHub sedang gangguan
        if ($userRes->status() === 404) {
            return response()->json([
                'success' => false,
                'message' => "Akun GitHub '{$username}' tidak ditemukan.",
            ], 404);
        }

        if ($userRes->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal berkomunikasi dengan layanan eksternal GitHub API.',
            ], 502); // 502 Bad Gateway: Server upstream/eksternal bermasalah
        }

        $reposRes = $client->get("https://api.github.com/users/{$username}/repos", [
            'sort' => 'updated',
            'per_page' => 3,
        ]);

        $profil = $userRes->json();
        $repos = collect($reposRes->json())->map(fn ($r) => [
            'repo_name' => $r['name'],
            'language' => $r['language'] ?? null,
            'stars' => $r['stargazers_count'] ?? 0,
            'html_url' => $r['html_url'] ?? null,
        ]);

        // 4. Kembalikan hasil orkestrasi yang sudah diringkas
        return response()->json([
            'success' => true,
            'message' => 'Data portofolio koding mahasiswa berhasil diambil dari GitHub API.',
            'data' => [
                'github_login' => $profil['login'],
                'display_name' => $profil['name'] ?? $profil['login'],
                'bio' => $profil['bio'] ?? null,
                'public_repos' => $profil['public_repos'] ?? 0,
                'followers' => $profil['followers'] ?? 0,
                'profile_url' => $profil['html_url'],
                'latest_repos' => $repos,
            ],
        ], 200);
    }
}
