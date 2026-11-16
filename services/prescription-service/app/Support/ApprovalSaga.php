<?php
namespace App\Support;
use Pims\Common\Exceptions\ServiceUnavailableException;
use Throwable;
final class ApprovalSaga
{
    public function run(callable $check, callable $approve, callable $deduct, callable $dispense, callable $rollback, callable $restore, ?callable $reconcile = null): void
    {
        $check(); $approve();
        try { $deduct(); } catch (Throwable $exception) {
            if ($exception instanceof ServiceUnavailableException && $reconcile !== null && !$reconcile()) {
                throw $exception;
            }
            $rollback();
            throw $exception;
        }
        try { $dispense(); } catch (Throwable $exception) { $restore(); $rollback(); throw $exception; }
    }
}
