import 'amenity_model.dart';
import 'user_model.dart';

class DachaMediaModel {
  final int id;
  final String url;
  final bool isMain;

  DachaMediaModel({
    required this.id,
    required this.url,
    this.isMain = false,
  });

  factory DachaMediaModel.fromJson(Map<String, dynamic> json) {
    return DachaMediaModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      url: json['url'] ?? json['file_path'] ?? '',
      isMain: json['is_main'] == true || json['is_main'] == 1,
    );
  }
}

class DachaModel {
  final int id;
  final int? userId;
  final String name;
  final String? description;
  final String region;
  final String district;
  final String? mahalla;
  final String? address;
  final double weekdayPrice;
  final double weekendPrice;
  final String currency;
  final String status;
  final int capacity;
  final int roomsCount;
  final double? latitude;
  final double? longitude;
  final double avgRating;
  final int reviewsCount;
  final List<DachaMediaModel> media;
  final List<AmenityModel> amenities;
  final UserModel? owner;

  DachaModel({
    required this.id,
    this.userId,
    required this.name,
    this.description,
    required this.region,
    required this.district,
    this.mahalla,
    this.address,
    required this.weekdayPrice,
    required this.weekendPrice,
    this.currency = 'USD',
    this.status = 'active',
    required this.capacity,
    required this.roomsCount,
    this.latitude,
    this.longitude,
    this.avgRating = 5.0,
    this.reviewsCount = 0,
    this.media = const [],
    this.amenities = const [],
    this.owner,
  });

  String get firstImageUrl {
    if (media.isNotEmpty) {
      final main = media.firstWhere((m) => m.isMain, orElse: () => media.first);
      return main.url;
    }
    return 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=600';
  }

  factory DachaModel.fromJson(Map<String, dynamic> json) {
    var mediaList = <DachaMediaModel>[];
    if (json['media'] is List) {
      mediaList = (json['media'] as List).map((i) => DachaMediaModel.fromJson(i)).toList();
    }

    var amenitiesList = <AmenityModel>[];
    if (json['amenities'] is List) {
      amenitiesList = (json['amenities'] as List).map((i) => AmenityModel.fromJson(i)).toList();
    }

    return DachaModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      userId: json['user_id'] != null ? int.tryParse(json['user_id'].toString()) : null,
      name: json['name'] ?? '',
      description: json['description'],
      region: json['region'] ?? '',
      district: json['district'] ?? '',
      mahalla: json['mahalla'],
      address: json['address'],
      weekdayPrice: double.tryParse(json['weekday_price']?.toString() ?? '0') ?? 0.0,
      weekendPrice: double.tryParse(json['weekend_price']?.toString() ?? json['weekday_price']?.toString() ?? '0') ?? 0.0,
      currency: json['currency'] ?? 'USD',
      status: json['status'] ?? 'active',
      capacity: int.tryParse(json['capacity']?.toString() ?? '1') ?? 1,
      roomsCount: int.tryParse(json['rooms_count']?.toString() ?? '1') ?? 1,
      latitude: json['latitude'] != null ? double.tryParse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.tryParse(json['longitude'].toString()) : null,
      avgRating: double.tryParse(json['avg_rating']?.toString() ?? '5.0') ?? 5.0,
      reviewsCount: int.tryParse(json['reviews_count']?.toString() ?? '0') ?? 0,
      media: mediaList,
      amenities: amenitiesList,
      owner: json['user'] != null || json['owner'] != null
          ? UserModel.fromJson(json['user'] ?? json['owner'])
          : null,
    );
  }
}
