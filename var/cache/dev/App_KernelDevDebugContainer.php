<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\Container98dYxGI\App_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/Container98dYxGI/App_KernelDevDebugContainer.php') {
    touch(__DIR__.'/Container98dYxGI.legacy');

    return;
}

if (!\class_exists(App_KernelDevDebugContainer::class, false)) {
    \class_alias(\Container98dYxGI\App_KernelDevDebugContainer::class, App_KernelDevDebugContainer::class, false);
}

return new \Container98dYxGI\App_KernelDevDebugContainer([
    'container.build_hash' => '98dYxGI',
    'container.build_id' => '7d2aedfe',
    'container.build_time' => 1607615198,
], __DIR__.\DIRECTORY_SEPARATOR.'Container98dYxGI');
