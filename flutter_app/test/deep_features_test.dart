import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:astrosubam_app/main.dart';

void main() {
  group('Deep Unit & Widget Tests - Tri-Lingual Localization & Astrologer Video Toggle', () {
    
    test('1. AstroLocale: Verify 3 Languages (Tamil, English, Hindi) Dictionary Keys', () {
      // 1. Check English translations
      AstroLocale.setLang('en');
      expect(AstroLocale.currentLang.value, 'en');
      expect(AstroLocale.t('app_title'), 'ASTROSUBAM');
      expect(AstroLocale.t('nav_home'), 'Home');
      expect(AstroLocale.t('video_na'), 'Video N/A');
      expect(AstroLocale.t('video_disabled_msg'), 'Video consultation is currently disabled for this astrologer.');

      // 2. Check Tamil translations
      AstroLocale.setLang('ta');
      expect(AstroLocale.currentLang.value, 'ta');
      expect(AstroLocale.t('nav_home'), 'முகப்பு');
      expect(AstroLocale.t('nav_astrologers'), 'ஜோதிடர்கள்');
      expect(AstroLocale.t('video_disabled_msg'), 'இந்த ஜோதிடருக்கு வீடியோ ஆலோசனை வசதி தற்போது முடக்கப்பட்டுள்ளது.');

      // 3. Check Hindi translations
      AstroLocale.setLang('hi');
      expect(AstroLocale.currentLang.value, 'hi');
      expect(AstroLocale.t('nav_home'), 'होम');
      expect(AstroLocale.t('nav_astrologers'), 'ज्योतिषी');
      expect(AstroLocale.t('video_disabled_msg'), 'इस ज्योतिषी के लिए वीडियो परामर्श वर्तमान में अक्षम है।');
    });

    testWidgets('2. AstrologerCard: Enabled Video Call shows active Video button', (WidgetTester tester) async {
      AstroLocale.setLang('en');
      final activeAstro = {
        'id': 10,
        'name': 'Acharya Shivananda',
        'speciality': 'Vedic Astrology',
        'languages': 'Tamil, English',
        'experience': 15,
        'rating': 4.9,
        'reviews': 120,
        'audioRate': 20.0,
        'videoRate': 35.0,
        'chatRate': 12.0,
        'is_video_enabled': true,
        'status': 'available',
        'call_status': 'available',
        'image': 'assets/logo.jpg',
      };

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: AstrologerCard(astrologer: activeAstro),
          ),
        ),
      );
      await tester.pump();

      // Video button should be visible with rate
      expect(find.text('Video ₹35/m'), findsOneWidget);
      expect(find.byIcon(Icons.videocam), findsOneWidget);
    });

    testWidgets('3. AstrologerCard: Disabled Video Call shows Video N/A and videocam_off', (WidgetTester tester) async {
      AstroLocale.setLang('en');
      final disabledVideoAstro = {
        'id': 11,
        'name': 'Pandit Raghavan',
        'speciality': 'KP Astrology',
        'languages': 'Tamil, Hindi',
        'experience': 18,
        'rating': 4.8,
        'reviews': 95,
        'audioRate': 25.0,
        'videoRate': 40.0,
        'chatRate': 15.0,
        'is_video_enabled': false,
        'status': 'available',
        'call_status': 'available',
        'image': 'assets/logo.jpg',
      };

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: AstrologerCard(astrologer: disabledVideoAstro),
          ),
        ),
      );
      await tester.pump();

      // Should show Video N/A button with videocam_off icon
      expect(find.text('Video N/A'), findsOneWidget);
      expect(find.byIcon(Icons.videocam_off), findsOneWidget);
      // Active video rate text should NOT be present
      expect(find.text('Video ₹40/m'), findsNothing);

      // Tap disabled button and verify SnackBar
      await tester.tap(find.text('Video N/A'));
      await tester.pump(); // Start animation
      await tester.pump(const Duration(milliseconds: 100)); // Advance frame

      expect(find.text('Video consultation is currently disabled for this astrologer.'), findsOneWidget);
    });

    testWidgets('4. Language Switcher Bottom Sheet switches language dynamically', (WidgetTester tester) async {
      AstroLocale.setLang('en');

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: Builder(
              builder: (ctx) => ElevatedButton(
                onPressed: () => AstroLocale.showLanguageSelector(ctx),
                child: const Text('Open Selector'),
              ),
            ),
          ),
        ),
      );

      // Open language bottom sheet
      await tester.tap(find.text('Open Selector'));
      await tester.pumpAndSettle();

      // Verify all 3 language tiles are rendered
      expect(find.text('Choose Language'), findsOneWidget);
      expect(find.text('🇮🇳 தமிழ் (Tamil)'), findsOneWidget);
      expect(find.text('🇬🇧 English'), findsOneWidget);
      expect(find.text('🇮🇳 हिन्दी (Hindi)'), findsOneWidget);

      // Select Tamil
      await tester.tap(find.text('🇮🇳 தமிழ் (Tamil)'));
      await tester.pumpAndSettle();

      // Verify language changed to 'ta'
      expect(AstroLocale.currentLang.value, 'ta');
      expect(AstroLocale.t('video_disabled_msg'), 'இந்த ஜோதிடருக்கு வீடியோ ஆலோசனை வசதி தற்போது முடக்கப்பட்டுள்ளது.');
    });

  });
}
