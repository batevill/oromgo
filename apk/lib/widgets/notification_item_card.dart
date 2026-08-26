import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/constants/app_colors.dart';
import '../core/utils/formatters.dart';
import '../models/notification_model.dart';
import '../providers/auth_provider.dart';
import '../providers/notification_provider.dart';

class NotificationItemCard extends StatelessWidget {
  final NotificationModel notification;

  const NotificationItemCard({
    super.key,
    required this.notification,
  });

  @override
  Widget build(BuildContext context) {
    final notifProvider = context.read<NotificationProvider>();
    final isOwner = context.watch<AuthProvider>().isOwner;

    Color badgeColor = AppColors.infoLight;
    Color badgeTextColor = AppColors.info;
    String badgeLabel = 'Xabar';
    String icon = 'ℹ️';

    if (notification.isBookingCreated) {
      badgeColor = AppColors.accentLight;
      badgeTextColor = AppColors.warning;
      badgeLabel = 'Yangi Bron';
      icon = '🔔';
    } else if (notification.isBookingConfirmed) {
      badgeColor = AppColors.successLight;
      badgeTextColor = AppColors.success;
      badgeLabel = 'Tasdiqlangan';
      icon = '✅';
    } else if (notification.isBookingCancelled) {
      badgeColor = AppColors.errorLight;
      badgeTextColor = AppColors.error;
      badgeLabel = 'Bekor qilingan';
      icon = '❌';
    } else if (notification.isReminder) {
      badgeColor = const Color(0xFFE0E7FF);
      badgeTextColor = const Color(0xFF4338CA);
      badgeLabel = 'Eslatma';
      icon = '⏰';
    }

    final showOwnerActions = isOwner &&
        notification.isBookingCreated &&
        notification.bookingId != null &&
        notification.status == 'pending';

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: notification.isRead ? AppColors.border : AppColors.primary,
          width: notification.isRead ? 1 : 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: () {
            notifProvider.markAsRead(notification.id);
          },
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header (Type badge, Title, Time ago)
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(icon, style: const TextStyle(fontSize: 18)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            notification.title,
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.dark),
                          ),
                          const SizedBox(height: 3),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: badgeColor,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              badgeLabel,
                              style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: badgeTextColor),
                            ),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      Formatters.formatTimeAgo(notification.createdAt),
                      style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                    ),
                  ],
                ),

                const SizedBox(height: 10),
                Text(
                  notification.message,
                  style: const TextStyle(fontSize: 13.5, color: AppColors.darkLight, height: 1.4),
                ),

                // Structured booking info grid
                if (notification.dachaName != null || notification.startDate != null) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.bgPage,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      children: [
                        if (notification.dachaName != null)
                          _buildDetailRow('🏡 Dacha:', notification.dachaName!),
                        if (notification.guestName != null)
                          _buildDetailRow('👤 Mijoz:', notification.guestName!),
                        if (notification.guestPhone != null)
                          _buildDetailRow('📞 Telefon:', notification.guestPhone!),
                        if (notification.startDate != null)
                          _buildDetailRow('📅 Sanalar:', '${notification.startDate} — ${notification.endDate ?? ""}'),
                        if (notification.totalPrice != null)
                          _buildDetailRow(
                            '💰 Summa:',
                            Formatters.formatCurrency(notification.totalPrice, currency: notification.currency ?? 'USD'),
                            valColor: AppColors.primary,
                          ),
                        if (notification.notes != null && notification.notes!.isNotEmpty)
                          _buildDetailRow('💬 Izoh:', notification.notes!),
                      ],
                    ),
                  ),
                ],

                // Action buttons for Dacha Owner
                if (showOwnerActions) ...[
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () async {
                            try {
                              await notifProvider.handleOwnerDecision(notification.bookingId!, 'confirm', notification.id);
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Bron muvaffaqiyatli tasdiqlandi!'), backgroundColor: AppColors.success),
                                );
                              }
                            } catch (e) {
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
                                );
                              }
                            }
                          },
                          icon: const Icon(Icons.check_circle_outline, size: 18, color: Colors.white),
                          label: const Text('Tasdiqlash', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Colors.white)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.success,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            padding: const EdgeInsets.symmetric(vertical: 10),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () async {
                            try {
                              await notifProvider.handleOwnerDecision(notification.bookingId!, 'reject', notification.id);
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Bron so\'rovi rad etildi.'), backgroundColor: AppColors.error),
                                );
                              }
                            } catch (e) {
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
                                );
                              }
                            }
                          },
                          icon: const Icon(Icons.cancel_outlined, size: 18, color: Colors.white),
                          label: const Text('Rad etish', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Colors.white)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.error,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            padding: const EdgeInsets.symmetric(vertical: 10),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {Color? valColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2.5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textMuted, fontWeight: FontWeight.w500)),
          Text(
            value,
            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: valColor ?? AppColors.dark),
          ),
        ],
      ),
    );
  }
}
