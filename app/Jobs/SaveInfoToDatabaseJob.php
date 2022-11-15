<?php

namespace App\Jobs;

use App\Models\Movies;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SaveInfoToDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $info;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($info)
    {
        $this->info = $info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('movies')->insert([
            'title' => $this->info['title'],
            'en_title' => $this->info['en_title'],
            'profile' => $this->info['profile'],
            'rank' => $this->info['rank'],
            'duration' => $this->info['duration'],
            'release_time' => $this->info['release_time'],
            'cover' => $this->info['cover'],
            'score' => $this->info['score']
        ]);
        $id = DB::getPdo()->lastInsertId();
        // 导演
        if (!empty($this->info['actor'])) {
            foreach ($this->info['actor'] as $actor) {
                DB::table('actors')->insert([
                    'movie_id' => $id,
                    'actor' => $actor
                ]);
            }
        }
        // 影片类别
        if (!empty($this->info['category'])) {
            foreach ($this->info['category'] as $category) {
                DB::table('categories')->insert([
                    'movie_id' => $id,
                    'category' => $category
                ]);
            }
        }
    }
}
