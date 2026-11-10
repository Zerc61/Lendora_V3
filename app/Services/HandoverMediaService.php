<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HandoverMediaService
{
    /**
     * @return array<string, mixed>
     */
    public function options(Request $request, Booking $booking): array
    {
        return [
            'checklist' => $request->input('checklist'),
            'accessories' => (array) $request->input('accessories', []),
            'item_notes' => (array) $request->input('item_notes', []),
            'shift_label' => $request->input('shift_label'),
            'photo_paths' => $this->storePhotos($request, $booking),
            'borrower_signature_path' => $this->storeSignature($request->input('borrower_signature'), $booking, 'borrower'),
            'operator_signature_path' => $this->storeSignature($request->input('operator_signature'), $booking, 'operator'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function storePhotos(Request $request, Booking $booking): array
    {
        $paths = [];

        foreach ((array) $request->file('photos', []) as $photo) {
            $paths[] = $photo->store("handovers/{$booking->code}", 'public');
        }

        return $paths;
    }

    private function storeSignature(?string $dataUrl, Booking $booking, string $role): ?string
    {
        if ($dataUrl === null || ! preg_match('#^data:image/png;base64,(.+)$#', $dataUrl, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[1], true);

        if ($binary === false || $binary === '') {
            return null;
        }

        $path = "handovers/{$booking->code}/{$role}-".now()->format('YmdHis').'.png';

        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
