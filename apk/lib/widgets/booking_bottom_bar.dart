import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';
import '../core/utils/formatters.dart';
import '../models/dacha_model.dart';
import 'custom_button.dart';

class BookingBottomBar extends StatelessWidget {
  final DachaModel dacha;
  final VoidCallback onBookTap;

  const BookingBottomBar({
    super.key,
    required this.dacha,
    required this.onBookTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 14,
        bottom: MediaQuery.of(context).padding.bottom + 14,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        border: const Border(top: BorderSide(color: AppColors.border)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.baseline,
                  textBaseline: TextBaseline.alphabetic,
                  children: [
                    Text(
                      Formatters.formatCurrency(dacha.weekdayPrice, currency: dacha.currency),
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                    ),
                    const Text(' / kun', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                  ],
                ),
                Text(
                  'Dam olish: ${Formatters.formatCurrency(dacha.weekendPrice, currency: dacha.currency)}',
                  style: const TextStyle(fontSize: 11.5, color: AppColors.accent, fontWeight: FontWeight.w600),
                ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: CustomButton(
              text: 'Bron qilish',
              onPressed: onBookTap,
              height: 48,
            ),
          ),
        ],
      ),
    );
  }
}
