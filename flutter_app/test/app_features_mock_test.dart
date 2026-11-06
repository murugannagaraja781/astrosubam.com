// test/app_features_mock_test.dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:astrosubam_app/main.dart';

void main() {
  setUpAll(() {
    // Gracefully ignore NetworkImage loading failures in headless widget test environment
    final originalOnError = FlutterError.onError;
    FlutterError.onError = (FlutterErrorDetails details) {
      if (details.exception.toString().contains('NetworkImage') ||
          details.exception.toString().contains('statusCode: 400') ||
          details.exception.toString().contains('image codec')) {
        return;
      }
      originalOnError?.call(details);
    };
  });

  setUp(() {
    UserState.walletBalance = 500.00;
    UserState.isLoggedIn = false;
    UserState.userName = 'Devotee User';
    UserState.userPhone = '8000000001';
    UserState.userId = 2;
    for (var a in UserState.astrologers) {
      a['image'] = 'assets/logo.jpg';
    }
  });

  group('AstroSubam 100% Mock Data Feature Verification', () {
    // -------------------------------------------------------------------------
    // TEST 1: APP LAUNCH AUTH SCREEN & MOBILE OTP LOGIN FLOW WITH TEST BYPASS
    // -------------------------------------------------------------------------
    testWidgets('1. Test App Launch Direct AuthScreen and OTP Login Flow', (WidgetTester tester) async {
      await tester.pumpWidget(const AstroSubamApp());
      await tester.pump();

      // Verify app launched directly on AuthScreen
      expect(find.text('ASTROSUBAM'), findsOneWidget);
      expect(find.text('பக்தர் உள்நுழைவு (Login)'), findsOneWidget);

      // Tap Send OTP
      final sendOtpBtn = find.text('Send OTP (OTP பெறுக)');
      expect(sendOtpBtn, findsOneWidget);
      await tester.tap(sendOtpBtn);
      await tester.pump();

      // Check Test Account Bypass Message
      expect(find.text('📱 Test Account Detected! Use Static OTP: 0101'), findsOneWidget);

      // Enter OTP 0101
      final otpFields = find.byType(TextField);
      await tester.enterText(otpFields.last, '0101');
      await tester.pump();

      // Tap Verify & Login
      final verifyBtn = find.text('Verify & Login');
      await tester.tap(verifyBtn);
      await tester.pumpAndSettle();

      // Verify Logged in state
      expect(UserState.isLoggedIn, isTrue);
      expect(UserState.userName.contains('0001'), isTrue);
    });

    // -------------------------------------------------------------------------
    // TEST 2: AUDIO CALL & 5-MIN SAFEGUARD
    // -------------------------------------------------------------------------
    testWidgets('2. Test Audio Call Consultation & 5-Min Balance Safeguard', (WidgetTester tester) async {
      final testAstro = UserState.astrologers[0]; // Guruji S. Sundaramurthy (Audio rate: 20)

      // Test 2.1: Insufficient Balance Safeguard
      UserState.walletBalance = 50.0; // Needs at least 5 * 20 = 100
      await tester.pumpWidget(MaterialApp(
        home: Scaffold(
          body: AstrologerCard(astrologer: testAstro),
        ),
      ));
      await tester.pump();

      final callBtn = find.text('Call ₹20/m');
      expect(callBtn, findsOneWidget);
      await tester.tap(callBtn);
      await tester.pump();

      // Safeguard Dialog should be visible
      expect(find.text('Insufficient Balance'), findsOneWidget);
      expect(find.textContaining('Minimum 5 minutes balance (₹100) is required'), findsOneWidget);

      // Dismiss dialog
      await tester.tap(find.text('Cancel'));
      await tester.pump();

      // Test 2.2: Launch Audio Consultation with Sufficient Balance
      UserState.walletBalance = 500.0;
      await tester.pumpWidget(MaterialApp(
        home: LiveAgoraCallScreen(
          astrologer: testAstro,
          ratePerMin: 20.0,
          isVideo: false,
        ),
      ));
      await tester.pump();

      expect(find.textContaining('Agora RTC Voice Call'), findsOneWidget);
      expect(find.text('Guruji S. Sundaramurthy'), findsOneWidget);
      expect(find.byIcon(Icons.mic), findsOneWidget);
      expect(find.byIcon(Icons.call_end), findsOneWidget);
    });

    // -------------------------------------------------------------------------
    // TEST 3: VIDEO CALL CONSULTATION & PIP CONTROLS
    // -------------------------------------------------------------------------
    testWidgets('3. Test Video Call Consultation with PiP Camera and Flip Toggle', (WidgetTester tester) async {
      final testAstro = UserState.astrologers[0]; // Video rate: 35

      await tester.pumpWidget(MaterialApp(
        home: LiveAgoraCallScreen(
          astrologer: testAstro,
          ratePerMin: 35.0,
          isVideo: true,
        ),
      ));
      await tester.pump();

      // Verify Fullscreen Video Call Header
      expect(find.textContaining('LIVE VIDEO'), findsOneWidget);
      expect(find.text('Guruji S. Sundaramurthy'), findsOneWidget);

      // Verify PiP Self Camera
      expect(find.text('Front Cam'), findsOneWidget);

      // Tap Flip Camera button
      final flipBtn = find.byIcon(Icons.flip_camera_ios);
      expect(flipBtn, findsOneWidget);
      await tester.tap(flipBtn);
      await tester.pump();

      // Now should show Rear Cam
      expect(find.text('Rear Cam'), findsOneWidget);

      // Toggle Camera On/Off
      final camBtn = find.byIcon(Icons.videocam);
      expect(camBtn, findsOneWidget);
      await tester.tap(camBtn);
      await tester.pump();

      expect(find.text('Cam Off'), findsOneWidget);
    });

    // -------------------------------------------------------------------------
    // TEST 4: LIVE CHAT CONSULTATION MESSAGING
    // -------------------------------------------------------------------------
    testWidgets('4. Test Live Chat Messaging with Astrologer', (WidgetTester tester) async {
      final testAstro = UserState.astrologers[0]; // Chat rate: 12

      await tester.pumpWidget(MaterialApp(
        home: LiveChatConsultationScreen(
          astrologer: testAstro,
          ratePerMin: 12.0,
        ),
      ));
      await tester.pump();

      // Initial Astrologer greeting
      expect(find.textContaining('Vanakkam! I am Guruji'), findsOneWidget);

      // Type birth details
      final inputField = find.byType(TextField);
      await tester.enterText(inputField, 'DOB: 15-08-1995, 10:30 AM, Chennai');
      await tester.pump();

      // Send Message
      final sendBtn = find.byIcon(Icons.send);
      await tester.tap(sendBtn);
      await tester.pump();

      // Verify user message is displayed in chat
      expect(find.text('DOB: 15-08-1995, 10:30 AM, Chennai'), findsOneWidget);

      // Wait for simulated astrologer response (1 second)
      await tester.pump(const Duration(seconds: 2));

      // Verify Astrologer response appeared
      expect(find.textContaining('Noted! Analyzing planetary transits'), findsOneWidget);
    });

    // -------------------------------------------------------------------------
    // TEST 5: WALLET RECHARGE & PAYMENT GATEWAY SHEET (UPI / CARDS / SUCCESS)
    // -------------------------------------------------------------------------
    testWidgets('5. Test Wallet Screen & Interactive Payment Gateway Sheet', (WidgetTester tester) async {
      UserState.walletBalance = 200.0;
      await tester.pumpWidget(const MaterialApp(
        home: WalletScreen(),
      ));
      await tester.pump();

      // Verify Wallet Screen Loaded
      expect(find.text('My Wallet / பணப்பை'), findsOneWidget);
      expect(find.text('₹200.00'), findsOneWidget);

      // Tap + ₹100 Recharge pack
      final packBtn = find.text('+ ₹100');
      expect(packBtn, findsOneWidget);
      await tester.tap(packBtn);
      await tester.pumpAndSettle();

      // Verify Payment Gateway Modal Sheet opened
      expect(find.text('AstroSubam Checkout'), findsOneWidget);
      expect(find.text('₹100'), findsOneWidget);
      expect(find.text('UPI (Instant)'), findsOneWidget);

      // Tap Pay Securely
      final payBtn = find.text('Pay ₹100 Securely ➔');
      expect(payBtn, findsOneWidget);
      await tester.tap(payBtn);
      await tester.pumpAndSettle();

      // Verify Payment Success Dialog
      expect(find.text('Payment Successful!'), findsOneWidget);
      expect(find.text('VERIFIED (PAID)'), findsOneWidget);
      expect(UserState.walletBalance, equals(300.0));
    });

    // -------------------------------------------------------------------------
    // TEST 6: ASTROLOGER LOGIN & MODERN VEDIC SPIRITUAL MINIMALIST DASHBOARD
    // -------------------------------------------------------------------------
    testWidgets('6. Test Astrologer Mobile Login & Vedic Dashboard Features', (WidgetTester tester) async {
      AstrologerState.isLoggedIn = false;
      UserState.isLoggedIn = false;

      await tester.pumpWidget(const AstroSubamApp());
      await tester.pumpAndSettle();

      // Tap Astrologer Role Tab
      final astroRoleTab = find.text('🧘‍♂️ ஜோதிடர் (Astrologer)');
      expect(astroRoleTab, findsOneWidget);
      await tester.tap(astroRoleTab);
      await tester.pumpAndSettle();

      // Tap Guruji Sundaramurthy chip
      final gurujiChip = find.text('9876543210 (Guruji Sundaramurthy)');
      expect(gurujiChip, findsOneWidget);
      await tester.tap(gurujiChip);
      await tester.pumpAndSettle();

      // Tap Send OTP
      final sendOtpBtn = find.text('Send OTP (OTP பெறுக)');
      await tester.ensureVisible(sendOtpBtn);
      await tester.pumpAndSettle();
      await tester.tap(sendOtpBtn);
      await tester.pumpAndSettle();

      // Verify OTP banner
      expect(find.text('📱 Test Account Detected! Use Static OTP: 0101'), findsOneWidget);

      // Enter OTP 0101
      final otpFields = find.byType(TextField);
      await tester.enterText(otpFields.last, '0101');
      await tester.pumpAndSettle();

      // Ensure Verify Button is visible and tap
      final verifyBtn = find.text('Verify & Login');
      await tester.ensureVisible(verifyBtn);
      await tester.pumpAndSettle();
      await tester.tap(verifyBtn);
      await tester.pumpAndSettle();

      // Verify Astrologer Dashboard is Loaded
      expect(AstrologerState.isLoggedIn, isTrue);
      expect(find.text('Guruji S. Sundaramurthy'), findsOneWidget);

      // Check Master Status Multi-Toggle Pill Bar
      expect(find.textContaining('Call: ON'), findsOneWidget);
      expect(find.textContaining('Chat: ON'), findsOneWidget);
      expect(find.textContaining('Video: ON'), findsOneWidget);

      // Check Metrics Quick-Glance Cards
      expect(find.text("Today's Earnings"), findsOneWidget);
      expect(find.text('Wallet Balance'), findsOneWidget);

      // Check Live Consultation Queue
      expect(find.text('Live Consultation Queue'), findsOneWidget);
      expect(find.text('Karthik R.'), findsOneWidget);
      expect(find.text('Priya M.'), findsOneWidget);

      // Check Quick Action Grid
      final kundaliBtn = find.text('Generate Kundali');
      await tester.ensureVisible(kundaliBtn);
      await tester.pumpAndSettle();
      expect(kundaliBtn, findsOneWidget);

      // Tap on "Generate Kundali" dialog
      await tester.tap(kundaliBtn);
      await tester.pumpAndSettle();
      expect(find.text('Vedic Kundali Generator'), findsOneWidget);
      await tester.tap(find.text('Done'));
      await tester.pumpAndSettle();

      // Tap on "Withdraw Payout" dialog
      final withdrawBtn = find.text('Withdraw Payout');
      await tester.ensureVisible(withdrawBtn);
      await tester.pumpAndSettle();
      await tester.tap(withdrawBtn);
      await tester.pumpAndSettle();
      expect(find.text('Withdraw Payout'), findsWidgets);
      expect(find.textContaining('HDFC Bank'), findsOneWidget);
      await tester.tap(find.text('Cancel'));
      await tester.pumpAndSettle();

      // Tap on "Simulate Incoming Call" utility tile to open Screen 2 (Incoming Alert Modal)
      final alertTile = find.text('Simulate Incoming Call');
      await tester.ensureVisible(alertTile);
      await tester.pumpAndSettle();
      await tester.tap(alertTile);
      await tester.pumpAndSettle();

      // Verify Incoming Call Alert Modal
      expect(find.text('INCOMING CALL'), findsOneWidget);
      expect(find.textContaining('Love & Marriage Compatibility'), findsOneWidget);
      expect(find.textContaining('Accept'), findsOneWidget);

      // Accept Consultation to open Screen 3 (Active Consultation with Live Kundali Drawer)
      final acceptBtn = find.textContaining('Accept');
      await tester.tap(acceptBtn);
      await tester.pumpAndSettle();

      // Verify Active Consultation Screen
      expect(find.text('Karthik R.'), findsWidgets);
      expect(find.text('Kundali'), findsOneWidget);
      expect(find.textContaining('Live Earnings:'), findsOneWidget);
      expect(find.textContaining('திருமணம் கைகூடும்'), findsOneWidget);
      expect(find.text('End'), findsOneWidget);

      // Open Live Kundali Drawer
      await tester.tap(find.text('Kundali'));
      await tester.pumpAndSettle();
      expect(find.text('Lagna Kundali (துலாம்)'), findsOneWidget);
      expect(find.text('ராசி'), findsWidgets);
    });
  });
}
