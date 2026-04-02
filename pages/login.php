<?php
// Login page – daisyUI + corporate theme
?>
<!DOCTYPE html>
<html lang="en" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <title>OSB Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/tailwind.css">
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
    <header class="navbar bg-primary text-primary-content shadow-md px-4">
        <div class="flex items-center gap-3">
            <img src="images/within_earth.png" alt="OSB" class="h-10 w-auto rounded-md bg-white/10 p-0.5">
            <span class="font-semibold tracking-wide text-base">OSB GLOBAL SERVICES</span>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-4">
        <div class="card bg-base-100 w-full max-w-md shadow-2xl border border-base-300">
            <div class="card-body items-center text-center pt-8 pb-2">
                <div class="bg-primary text-primary-content rounded-2xl w-14 h-14 flex items-center justify-center shadow-lg mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="card-title justify-center text-2xl font-semibold">Secure Sign In</h1>
                <p class="text-base-content/70 text-sm">Access your business travel portal</p>
            </div>
            <div class="card-body pt-0">
                <?php if (!empty($error)): ?>
                    <div role="alert" class="alert alert-error text-sm mb-2">
                        <span><?= h($error) ?></span>
                    </div>
                <?php endif; ?>
                <form method="post" action="index.php?page=login" class="space-y-4">
                    <fieldset class="fieldset">
                        <label class="fieldset-label font-medium">Username</label>
                        <label class="input input-bordered flex items-center gap-2 w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input type="text" name="username" class="grow" placeholder="Your username" required autocomplete="username">
                        </label>

                        <label class="fieldset-label font-medium mt-2">Password</label>
                        <label class="input input-bordered flex items-center gap-2 w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <input type="password" name="password" class="grow" placeholder="Password" required autocomplete="current-password">
                        </label>
                        <div class="flex justify-end mt-1">
                            <a href="#" class="link link-hover text-error text-sm">Forgot password?</a>
                        </div>
                    </fieldset>
                    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer footer-center bg-base-300 text-base-content/70 p-4 text-xs">
        <aside>
            <p>&copy; <?= date('Y') ?> Within Earth Holidays Sdn Bhd. All rights reserved.</p>
        </aside>
    </footer>
</body>
</html>
