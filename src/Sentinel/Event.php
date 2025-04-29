<?php

set_error_handler([Factory::class, 'handlePhpError']);
set_exception_handler([Factory::class, 'handleException']);
register_shutdown_function([Factory::class, 'handleShutdown']);
