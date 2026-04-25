<?php

namespace OneShot\Activity\Services;

use OneShot\Core\Services\Base;
use OneShot\Activity\Models\Log;

class Activity extends Base
{
    public function log(
        ?int    $userId,
        string  $action,
        ?string $subjectType = null,
        ?int    $subjectId   = null,
        array   $metadata    = [],
        ?string $ip          = null
    ): void {
        (new Log())->insertLog([
            'user_id'      => $userId,
            'action'       => $action,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'metadata'     => $metadata ? json_encode($metadata) : null,
            'ip'           => $ip,
        ]);
    }
}
