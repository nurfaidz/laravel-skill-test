<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled posts that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scheduledPosts = Post::where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        if ($scheduledPosts->isEmpty()) {
            $this->info('No scheduled posts to publish.');

            return;
        }

        foreach ($scheduledPosts as $post) {
            $post->is_draft = false;
            $post->published_at = now();
            $post->save();

            $this->info("Published post: {$post->title} (ID: {$post->id})");
        }
    }
}
