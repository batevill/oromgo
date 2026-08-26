import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/custom_button.dart';
import '../owner/owner_dashboard_screen.dart';
import 'auth_modal.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil 👤', style: TextStyle(fontWeight: FontWeight.w800)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // User Header Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 16, offset: const Offset(0, 4)),
                ],
              ),
              child: auth.isAuthenticated && user != null
                  ? Row(
                      children: [
                        CircleAvatar(
                          radius: 32,
                          backgroundColor: AppColors.primaryLight,
                          child: Text(
                            user.name.isNotEmpty ? user.name[0] : 'U',
                            style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.primary),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user.name,
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                user.phone ?? user.email ?? 'Telefon ko\'rsatilmagan',
                                style: const TextStyle(fontSize: 13, color: AppColors.textMuted),
                              ),
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                decoration: BoxDecoration(
                                  color: user.isOwner ? AppColors.accentLight : AppColors.primaryLight,
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  user.isOwner ? '👑 Dacha Egasi' : '👤 Mijoz',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w800,
                                    color: user.isOwner ? AppColors.warning : AppColors.primary,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    )
                  : Column(
                      children: [
                        const Text('👋 Xush kelibsiz!', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                        const SizedBox(height: 6),
                        const Text('Barcha imkoniyatlardan foydalanish uchun profilingizga kiring.', textAlign: TextAlign.center, style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                        const SizedBox(height: 16),
                        CustomButton(
                          text: 'Tizimga kirish',
                          onPressed: () => AuthModal.show(context),
                        ),
                      ],
                    ),
            ),

            const SizedBox(height: 24),

            // Settings & Actions Menu
            if (auth.isAuthenticated && user != null) ...[
              // Owner Management Shortcut
              if (user.isOwner)
                _buildMenuItem(
                  icon: Icons.dashboard_customize_outlined,
                  title: 'Dacha Egasi Paneli',
                  subtitle: 'Bronlar, kalendarni yopish va boshqaruv',
                  iconColor: AppColors.primary,
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const OwnerDashboardScreen()),
                    );
                  },
                ),

              // Telegram Bot Link
              _buildMenuItem(
                icon: Icons.send_rounded,
                title: 'Telegram Botga bog\'lanish',
                subtitle: user.hasTelegram ? '✅ Ulandi (${user.telegramId})' : 'Xabarnomalarni Telegramda olish',
                iconColor: AppColors.telegram,
                onTap: () async {
                  final link = await context.read<NotificationProvider>().getTelegramBotLink();
                  if (link != null) {
                    final uri = Uri.parse(link);
                    if (await canLaunchUrl(uri)) {
                      await launchUrl(uri, mode: LaunchMode.externalApplication);
                    }
                  }
                },
              ),

              _buildMenuItem(
                icon: Icons.swap_horiz_rounded,
                title: 'Boshqa akkauntga o\'tish (Test)',
                subtitle: 'Dacha egasi yoki mijoz profilini almashtirish',
                iconColor: AppColors.accent,
                onTap: () => AuthModal.show(context),
              ),

              _buildMenuItem(
                icon: Icons.logout_rounded,
                title: 'Chiqish',
                subtitle: 'Tizimdan chiqish',
                iconColor: AppColors.error,
                onTap: () async {
                  await auth.logout();
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Tizimdan chiqdingiz.'), backgroundColor: AppColors.success),
                    );
                  }
                },
              ),
            ],

            const SizedBox(height: 30),
            const Text(
              'Oromgo Mobile v1.0.0\n© 2026 Oromgo.uz',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.5),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color iconColor,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: iconColor.withOpacity(0.12), borderRadius: BorderRadius.circular(12)),
          child: Icon(icon, color: iconColor, size: 22),
        ),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14.5, color: AppColors.dark)),
        subtitle: Text(subtitle, style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
        trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.textMuted),
        onTap: onTap,
      ),
    );
  }
}
