<?php

namespace DuncanMcClean\Cargo\Http\Controllers;

use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Http\Request;
use Statamic\Exceptions\ForbiddenHttpException;
use Statamic\Exceptions\NotFoundHttpException;
use ZipArchive;

class ProductDownloadController
{
    // todo: confirm controller name / url

    public function __invoke(Request $request, $orderId, $lineItem)
    {
        if (! $request->hasValidSignature()) {
            throw new ForbiddenHttpException;
        }

        throw_unless($order = Order::find($orderId), NotFoundHttpException::class);
        throw_unless($lineItem = $order->lineItems()->find($lineItem), NotFoundHttpException::class);

        $product = $lineItem->variant()
            ? $lineItem->variant()
            : $lineItem->product();

        if (! $product->get('downloads')) {
            throw new NotFoundHttpException;
        }

        if (
            $product->get('download_limit')
            && $lineItem->get('download_count')
            && $lineItem->download_count >= $product->get('download_limit')
        ) {
            throw new ForbiddenHttpException;
        }

        $lineItem->download_count++;
        $order->save();

        if ($product->downloads?->count() > 1) {
            $zip = new ZipArchive;
            $zip->open($path = storage_path("cargo_download_{$order->id()}_{$lineItem->id()}.zip"), ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($product->downloads as $asset) {
                $zip->addFile($asset->resolvedPath(), $asset->basename());
            }

            $zip->close();

            return response()->download($path, "{$lineItem->product()->slug()}.zip")->deleteFileAfterSend();
        }

        $asset = $product->downloads->first();

        return response()->download($asset->resolvedPath(), $asset->basename());
    }
}
