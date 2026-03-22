<?php

namespace App\Models;

/**
 * Compatibility adapter.
 *
 * The codebase historically uses the legacy `App\User` model (app/User.php).
 * Some newer parts reference `App\Models\User`, so we provide this thin bridge.
 */
class User extends \App\User
{
}

