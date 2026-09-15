<?php



declare(strict_types=1);



namespace Componist\DeveloperTools\Http\Controllers;



use Componist\Backup\Jobs\CreateDatabaseDumpJob;

use Componist\Core\Models\ComponistCoreNotification;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\RedirectResponse;

use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Log;



class DevController extends Controller

{

    public function index(): JsonResponse

    {

        abort_unless(Gate::allows('manage-developer-tools'), 403);



        $user = Auth::user();

        $recipient = $user?->email ?? $user?->id;



        if ($recipient === null) {

            return response()->json([

                'status' => 'error',

                'message' => 'Kein eingeloggter Benutzer für die Test-Benachrichtigung.',

            ], 422);

        }



        $result = ComponistCoreNotification::CreateMessage($recipient, 'Test-Benachrichtigung', 'Smoke-/Dev-Testnachricht');



        if ($result === null) {

            return response()->json([

                'status' => 'error',

                'message' => 'Test-Benachrichtigung konnte nicht erstellt werden.',

            ], 422);

        }



        Log::info('ComponistCoreNotification erstellt', ['result' => $result->getKey()]);



        return response()->json(['status' => 'created', 'id' => $result->getKey()]);

    }



    public function backup(): RedirectResponse

    {

        abort_unless(Gate::allows('manage-developer-tools'), 403);



        CreateDatabaseDumpJob::dispatch();



        return redirect()

            ->route('componist.developer-tools.index')

            ->with('success', 'Backup-Job wurde gestartet.');

    }

}

