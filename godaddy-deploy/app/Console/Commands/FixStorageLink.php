<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStorageLink extends Command
{
    protected $signature = 'storage:link';
    protected $description = 'Create a storage link in a Windows-friendly way';

    public function handle()
    {
        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');

        // Remove existing link or directory if it exists
        if (is_link($publicPath) || is_dir($publicPath)) {
            if (PHP_OS_FAMILY === 'Windows') {
                // On Windows, we need to remove directory junctions differently
                shell_exec('rmdir "' . str_replace('/', '\\', $publicPath) . '"');
            } else {
                File::delete($publicPath);
            }
        }

        // Create the storage/app/public directory if it doesn't exist
        if (!is_dir($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        // Create the symlink/junction
        if (PHP_OS_FAMILY === 'Windows') {
            // Use directory junction on Windows (doesn't require admin privileges)
            shell_exec('mklink /J "' . str_replace('/', '\\', $publicPath) . '" "' . str_replace('/', '\\', $storagePath) . '"');
            $this->info('Storage directory junction created successfully.');
        } else {
            // Use symlink on Unix systems
            File::link($storagePath, $publicPath);
            $this->info('Storage symlink created successfully.');
        }

        return Command::SUCCESS;
    }
} 