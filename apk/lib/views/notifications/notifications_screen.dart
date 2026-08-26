import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/notification_item_card.dart';
import '../profile/auth_modal.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (context.read<AuthProvider>().isAuthenticated) {
        context.read<NotificationProvider>().fetchNotifications();
      }
    });
  }

  Future<void> _openTelegramBot() async {
    final notifProvider = context.read<NotificationProvider>();
    final link = await notifProvider.getTelegramBotLink();
    if (link != null) {
      final uri = Uri.parse(link);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final notifProvider = context.watch<NotificationProvider>();

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(title: const Text('Bildirishnomalar')),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('🔔', style: TextStyle(fontSize: 56)),
                const SizedBox(height: 16),
                const Text(
                  'Bildirishnomalarni ko\'rish uchun kiring',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Bron so\'rovlari, tasdiqlashlar va Telegram bot xabarlarini real vaqtda kuzating.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 13.5, color: AppColors.textMuted),
                ),
                const SizedBox(height: 24),
                CustomButton(
                  text: 'Tizimga kirish',
                  onPressed: () => AuthModal.show(context),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Bildirishnomalar 🔔', style: TextStyle(fontWeight: FontWeight.w800)),
        actions: [
          if (notifProvider.notifications.isNotEmpty)
            TextButton(
              onPressed: () => notifProvider.markAllAsRead(),
              child: const Text('Barchasini o\'qish', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 12.5)),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => notifProvider.fetchNotifications(),
        color: AppColors.primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Telegram Bot Promo Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFF0F9FF), Color(0xFFE0F2FE)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFFBAE6FD)),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: const BoxDecoration(color: AppColors.telegram, shape: BoxShape.circle),
                      child: const Icon(Icons.send_rounded, color: Colors.white, size: 22),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Telegram Botga ulanish', style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: Color(0xFF0C4A6E))),
                          const SizedBox(height: 2),
                          Text(
                            notifProvider.hasTelegramLinked
                                ? '✅ Bot muvaffaqiyatli ulangan'
                                : 'Bronlarni Telegramda 1 bosishda tasdiqlash uchun botni ulang.',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF0369A1)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton(
                      onPressed: _openTelegramBot,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.telegramDark,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      child: Text(
                        notifProvider.hasTelegramLinked ? 'Ochish' : 'Ulanish',
                        style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              if (notifProvider.isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: CircularProgressIndicator(color: AppColors.primary),
                  ),
                )
              else if (notifProvider.notifications.isEmpty)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.symmetric(vertical: 60),
                    child: Column(
                      children: [
                        Text('📭', style: TextStyle(fontSize: 48)),
                        SizedBox(height: 12),
                        Text(
                          'Hozircha bildirishnomalar yo\'q',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.dark),
                        ),
                        SizedBox(height: 4),
                        Text('Yangi bronlar va statuslar shu yerda paydo bo\'ladi.', style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                      ],
                    ),
                  ),
                )
              else
                ...notifProvider.notifications.map((n) => NotificationItemCard(notification: n)),
            ],
          ),
        ),
      ),
    );
  }
}
