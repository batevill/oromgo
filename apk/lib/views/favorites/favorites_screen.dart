import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/favorite_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/dacha_card.dart';
import '../explore/dacha_detail_screen.dart';
import '../profile/auth_modal.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (context.read<AuthProvider>().isAuthenticated) {
        context.read<FavoriteProvider>().fetchFavorites();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final favProvider = context.watch<FavoriteProvider>();

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(title: const Text('Sevimlilar')),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('❤️', style: TextStyle(fontSize: 56)),
                const SizedBox(height: 16),
                const Text(
                  'Sevimlilar ro\'yxati bo\'sh',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                ),
                const SizedBox(height: 6),
                const Text(
                  'O\'zingizga yoqqan dachalarni saqlab qo\'yish uchun tizimga kiring.',
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
        title: const Text('Sevimlilarim ❤️', style: TextStyle(fontWeight: FontWeight.w800)),
      ),
      body: RefreshIndicator(
        onRefresh: () => favProvider.fetchFavorites(),
        color: AppColors.primary,
        child: favProvider.isLoading
            ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
            : favProvider.favorites.isEmpty
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Text('🤍', style: TextStyle(fontSize: 56)),
                          const SizedBox(height: 16),
                          const Text(
                            'Hali hech qanday dacha saqlanmagan',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Asosiy sahifadagi yurakcha belgisini bosib, dachalarni sevimlilarga qo\'shishingiz mumkin.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 13.5, color: AppColors.textMuted),
                          ),
                        ],
                      ),
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(20),
                    itemCount: favProvider.favorites.length,
                    itemBuilder: (context, idx) {
                      final dacha = favProvider.favorites[idx];
                      return DachaCard(
                        dacha: dacha,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => DachaDetailScreen(dachaId: dacha.id),
                            ),
                          );
                        },
                      );
                    },
                  ),
      ),
    );
  }
}
