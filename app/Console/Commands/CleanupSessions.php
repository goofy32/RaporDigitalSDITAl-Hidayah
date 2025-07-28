<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'session:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired sessions from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lifetime = config('session.lifetime') * 60; // Convert to seconds
        $expiredTime = time() - $lifetime;

        // Count expired sessions
        $expiredCount = DB::table('sessions')
            ->where('last_activity', '<', $expiredTime)
            ->count();

        if ($expiredCount === 0) {
            $this->info('No expired sessions found.');
            return 0;
        }

        $this->info("Found {$expiredCount} expired sessions.");

        if (!$this->option('force') && !$this->confirm('Do you want to delete these expired sessions?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Delete expired sessions
        $deleted = DB::table('sessions')
            ->where('last_activity', '<', $expiredTime)
            ->delete();

        $this->info("Successfully deleted {$deleted} expired sessions.");

        // Also clean up any orphaned sessions (sessions without valid users)
        $orphanedCount = DB::table('sessions')
            ->whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->select('id')
                    ->from('users')
                    ->whereColumn('users.id', 'sessions.user_id');
            })
            ->whereNotExists(function ($query) {
                $query->select('id')
                    ->from('gurus')
                    ->whereColumn('gurus.id', 'sessions.user_id');
            })
            ->count();

        if ($orphanedCount > 0) {
            if ($this->option('force') || $this->confirm("Found {$orphanedCount} orphaned sessions. Delete them too?")) {
                $orphanedDeleted = DB::table('sessions')
                    ->whereNotNull('user_id')
                    ->whereNotExists(function ($query) {
                        $query->select('id')
                            ->from('users')
                            ->whereColumn('users.id', 'sessions.user_id');
                    })
                    ->whereNotExists(function ($query) {
                        $query->select('id')
                            ->from('gurus')
                            ->whereColumn('gurus.id', 'sessions.user_id');
                    })
                    ->delete();

                $this->info("Successfully deleted {$orphanedDeleted} orphaned sessions.");
            }
        }

        return 0;
    }
}