<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Inertia\Inertia;

class RenderVideoController extends Controller
{

    private function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    public function render(Request $request)
    {
        // Obtén los videos desde la solicitud
        $videos = $request->input('videos');

        array_unshift($videos, 'user_videos/like_and_sub.mp4');

        // Verifica si hay al menos dos videos
        if (count($videos) < 2) {
            return response()->json([
                'error' => 'Se necesitan al menos dos videos para combinar.'
            ], 400);
        }

        // Normaliza cada video y guarda las rutas de los videos normalizados
        $normalizedVideos = [];
        foreach ($videos as $index => $video) {
            $inputPath = Storage::disk('public')->path($video);
            $outputPath = storage_path('app/public/user_videos/normalized_video_' . $index . '.mp4');

            // Comando de FFmpeg para normalizar el video
            $process = new Process([
                'ffmpeg',
                '-i', $inputPath,
                '-vf', 'scale=1920:1080', // Cambia la resolución a 1920x1080 (HD)
                '-r', '60', // Fija la tasa de cuadros a 60 fps
                '-c:v', 'libx264', // Re-codifica el video con códec H.264
                '-preset', 'fast', // Preset de velocidad
                '-crf', '23', // Control de calidad
                '-c:a', 'aac', // Re-codifica el audio con códec AAC
                '-b:a', '192k', // Tasa de bits para el audio
                '-strict', '-2',
                '-y', // Sobrescribe archivos existentes sin preguntar
                $outputPath
            ]);

            $process->setTimeout(90000);
            try {
                $process->mustRun();
                $normalizedVideos[] = $outputPath;
            } catch (ProcessFailedException $exception) {
                // Log the error and continue to the next video
                \Log::error("Error al normalizar el video {$inputPath}: " . $exception->getMessage());
                continue;
            }
        }

        // Verifica si hay al menos dos videos normalizados
        if (count($normalizedVideos) < 2) {
            return response()->json([
                'error' => 'Se necesitan al menos dos videos válidos para combinar.'
            ], 400);
        }

        // Crea el archivo de lista de archivos para ffmpeg
        $fileListPath = storage_path('app/public/user_videos/ffmpeg_list.txt');
        $fileListContent = '';
        foreach ($normalizedVideos as $videoPath) {
            $fileListContent .= "file '" . $videoPath . "'\n";
        }
        file_put_contents($fileListPath, $fileListContent);

        // Ruta para el video combinado
        $outputVideoPath = storage_path('app/public/user_videos/combined_video.mp4');

        // Comando de FFmpeg para concatenar los videos normalizados
        $process = new Process([
            'ffmpeg',
            '-f', 'concat',
            '-safe', '0',
            '-i', $fileListPath,
            '-c:v', 'libx264', // Codificación eficiente del video
            '-crf', '23',      // Nivel de calidad
            '-preset', 'fast', // Preset de velocidad
            '-c:a', 'aac',     // Re-codificar el audio
            '-b:a', '192k',    // Tasa de bits para el audio
            '-movflags', '+faststart', // Hacer que el video inicie rápido en la web
            '-y', // Sobrescribe el archivo de salida existente
            $outputVideoPath
        ]);

        $process->setTimeout(90000);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json([
                'error' => 'Error al combinar los videos: ' . $exception->getMessage()
            ], 500);
        }

        // Limpia el archivo de lista de archivos
        unlink($fileListPath);

        // Limpia los archivos normalizados temporales
        foreach ($normalizedVideos as $videoPath) {
            unlink($videoPath);
        }

        // Generar un nombre aleatorio
        $randomName = 'user_videos/' . $this->generateRandomString() . '.mp4';

        // Asegurarse de que la carpeta exista (si no, crearla)
        $destinationPath = public_path('user_videos');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Mover el archivo a la carpeta 'public/user_videos'
        $sourcePath = storage_path('app/public/user_videos/combined_video.mp4');
        $newPath = $destinationPath . '/' . basename($randomName);
        rename($sourcePath, $newPath);

        // redirect back with url
        return redirect()->back()->with('url', 'user_videos/' . basename($randomName));
    }
}
