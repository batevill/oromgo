import 'user_model.dart';

class ReviewModel {
  final int id;
  final int dachaId;
  final int userId;
  final int rating;
  final String? comment;
  final String? createdAt;
  final UserModel? user;

  ReviewModel({
    required this.id,
    required this.dachaId,
    required this.userId,
    required this.rating,
    this.comment,
    this.createdAt,
    this.user,
  });

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    return ReviewModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      dachaId: int.tryParse(json['dacha_id']?.toString() ?? '0') ?? 0,
      userId: int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      rating: int.tryParse(json['rating']?.toString() ?? '5') ?? 5,
      comment: json['comment'],
      createdAt: json['created_at'],
      user: json['user'] != null ? UserModel.fromJson(json['user']) : null,
    );
  }
}
