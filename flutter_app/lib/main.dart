import 'dart:async';
import 'package:flutter/material.dart';

void main() {
  runApp(const AstroSubamApp());
}

class AstroSubamApp extends StatelessWidget {
  const AstroSubamApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'AstroSubam - Astrology & Consultations',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        brightness: Brightness.dark,
        scaffoldBackgroundColor: const Color(0xFF090A15),
        primaryColor: const Color(0xFFFFD700),
        colorScheme: const ColorScheme.dark(
          primary: Color(0xFFFFD700),
          secondary: Color(0xFFF39C12),
          surface: Color(0xFF14172C),
        ),
        fontFamily: 'Roboto',
      ),
      home: const MainNavigationScreen(),
    );
  }
}

// App State Manager
class UserState {
  static double walletBalance = 500.00;
  static String userName = 'Devotee User';
  static String userPhone = '+91 99988 87770';

  static List<Map<String, dynamic>> astrologers = [
    {
      'id': 1,
      'name': 'Guruji S. Sundaramurthy',
      'speciality': 'Vedic Astrology • KP System • Marriage Compatibility',
      'languages': 'Tamil, English',
      'experience': 22,
      'rating': 4.95,
      'reviews': 450,
      'audioRate': 20.0,
      'videoRate': 35.0,
      'chatRate': 12.0,
      'status': 'available', // available | busy
      'image': 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
    },
    {
      'id': 2,
      'name': 'Dr. K. Jayalakshmi (Gold Medalist)',
      'speciality': 'Nadi Astrology • Career & Business Astro • Gemology',
      'languages': 'Tamil, English, Telugu',
      'experience': 16,
      'rating': 4.88,
      'reviews': 312,
      'audioRate': 18.0,
      'videoRate': 30.0,
      'chatRate': 10.0,
      'status': 'available',
      'image': 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80',
    },
    {
      'id': 3,
      'name': 'Acharya Ramanan Sarma',
      'speciality': 'Prasanna Jyothidam • Dosha Nivarana • Vasthu Sastra',
      'languages': 'Tamil, Sanskrit, Hindi',
      'experience': 14,
      'rating': 4.92,
      'reviews': 280,
      'audioRate': 15.0,
      'videoRate': 25.0,
      'chatRate': 10.0,
      'status': 'available',
      'image': 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
    },
    {
      'id': 4,
      'name': 'Astrologer Meenakshi Ammal',
      'speciality': 'Numerology • Tarot Reader • Child Horoscope',
      'languages': 'Tamil, Malayalam',
      'experience': 19,
      'rating': 4.91,
      'reviews': 390,
      'audioRate': 16.0,
      'videoRate': 28.0,
      'chatRate': 10.0,
      'status': 'available',
      'image': 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
    },
  ];
}

// Bottom Navigation Screen
class MainNavigationScreen extends StatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;

  final List<Widget> _pages = [
    const HomeScreen(),
    const AstrologersListScreen(),
    const DailyKundliScreen(),
    const WalletScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _pages[_currentIndex],
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          border: Border(top: BorderSide(color: Color(0xFF232742), width: 1)),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          backgroundColor: const Color(0xFF101225),
          selectedItemColor: const Color(0xFFFFD700),
          unselectedItemColor: Colors.white54,
          type: BottomNavigationBarType.fixed,
          onTap: (index) => setState(() => _currentIndex = index),
          items: const [
            BottomNavigationBarItem(icon: Icon(Icons.temple_hindu), label: 'Home'),
            BottomNavigationBarItem(icon: Icon(Icons.people_alt_rounded), label: 'Astrologers'),
            BottomNavigationBarItem(icon: Icon(Icons.auto_awesome), label: 'Kundli'),
            BottomNavigationBarItem(icon: Icon(Icons.account_balance_wallet_rounded), label: 'Wallet'),
          ],
        ),
      ),
    );
  }
}

// 1. HOME SCREEN
class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF101225),
        elevation: 0,
        title: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.asset('assets/logo.jpg', height: 38, width: 38, fit: BoxFit.cover),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'ASTROSUBAM',
                  style: TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold, fontSize: 16, letterSpacing: 1.2),
                ),
                Text('Auspicious Life Guidance', style: TextStyle(color: Colors.white70, fontSize: 10)),
              ],
            ),
          ],
        ),
        actions: [
          GestureDetector(
            onTap: () {
              Navigator.push(context, MaterialPageRoute(builder: (_) => const WalletScreen()));
            },
            child: Container(
              margin: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFF1F2445),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFFFD700), width: 1),
              ),
              child: Row(
                children: [
                  const Icon(Icons.account_balance_wallet, color: Color(0xFFFFD700), size: 16),
                  const SizedBox(width: 6),
                  Text(
                    '₹${UserState.walletBalance.toStringAsFixed(0)}',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Hero Banner
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF281E48), Color(0xFF131530)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFFFD700).withAlpha(77)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: const [
                        Icon(Icons.stars, color: Color(0xFFFFD700), size: 20),
                        SizedBox(width: 8),
                        Text('100% Authentic Vedic Consultation', style: TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.w600, fontSize: 12)),
                      ],
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'Talk or Chat with Verified\nAstrologers Live',
                      style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold, height: 1.3),
                    ),
                    const SizedBox(height: 14),
                    ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFFFD700),
                        foregroundColor: Colors.black,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                      ),
                      onPressed: () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const AstrologersListScreen()));
                      },
                      icon: const Icon(Icons.call, size: 18),
                      label: const Text('Consult Now', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Quick Actions
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _quickAction(context, Icons.call, 'Live Call', const Color(0xFFE67E22), () {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const AstrologersListScreen()));
                  }),
                  _quickAction(context, Icons.videocam, 'Video Call', const Color(0xFF9B59B6), () {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const AstrologersListScreen()));
                  }),
                  _quickAction(context, Icons.chat, 'Live Chat', const Color(0xFF2ECC71), () {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const AstrologersListScreen()));
                  }),
                  _quickAction(context, Icons.calculate, 'Kundli', const Color(0xFF3498DB), () {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const DailyKundliScreen()));
                  }),
                ],
              ),

              const SizedBox(height: 28),

              // Available Astrologers Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Available Astrologers', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                  TextButton(
                    onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AstrologersListScreen())),
                    child: const Text('View All', style: TextStyle(color: Color(0xFFFFD700))),
                  ),
                ],
              ),

              const SizedBox(height: 10),

              // Astrologer Cards List
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: UserState.astrologers.length,
                itemBuilder: (context, index) {
                  final astro = UserState.astrologers[index];
                  return AstrologerCard(astrologer: astro);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  static Widget _quickAction(BuildContext context, IconData icon, String label, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: color.withAlpha(46),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: color.withAlpha(128)),
            ),
            child: Icon(icon, color: color, size: 26),
          ),
          const SizedBox(height: 8),
          Text(label, style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

// 2. ASTROLOGERS LIST SCREEN
class AstrologersListScreen extends StatelessWidget {
  const AstrologersListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Top Astrologers', style: TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF101225),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: UserState.astrologers.length,
        itemBuilder: (context, index) {
          final astro = UserState.astrologers[index];
          return AstrologerCard(astrologer: astro);
        },
      ),
    );
  }
}

// Astrologer Card Component
class AstrologerCard extends StatelessWidget {
  final Map<String, dynamic> astrologer;

  const AstrologerCard({super.key, required this.astrologer});

  @override
  Widget build(BuildContext context) {
    final double audioRate = astrologer['audioRate'];
    final double chatRate = astrologer['chatRate'];

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFF14172C),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF262A4E)),
      ),
      child: Column(
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar with Online Badge
              Stack(
                children: [
                  CircleAvatar(
                    radius: 34,
                    backgroundImage: NetworkImage(astrologer['image']),
                  ),
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: Container(
                      width: 14,
                      height: 14,
                      decoration: BoxDecoration(
                        color: Colors.green,
                        shape: BoxShape.circle,
                        border: Border.all(color: const Color(0xFF14172C), width: 2),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 14),
              // Details
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      astrologer['name'],
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      astrologer['speciality'],
                      style: const TextStyle(color: Colors.white70, fontSize: 11),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Exp: ${astrologer['experience']} yrs • ${astrologer['languages']}',
                      style: const TextStyle(color: Colors.white54, fontSize: 11),
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.star, color: Color(0xFFFFD700), size: 15),
                        const SizedBox(width: 4),
                        Text(
                          '${astrologer['rating']} (${astrologer['reviews']} orders)',
                          style: const TextStyle(color: Color(0xFFFFD700), fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 12),
          const Divider(color: Color(0xFF232742), height: 1),
          const SizedBox(height: 10),

          // CTAs (Audio Call, Video Call, Chat)
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFF2ECC71),
                    side: const BorderSide(color: Color(0xFF2ECC71)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                  ),
                  onPressed: () => _startConsultation(context, 'chat', chatRate),
                  icon: const Icon(Icons.chat_bubble_outline, size: 16),
                  label: Text('Chat ₹${chatRate.toInt()}/m', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFFD700),
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                  ),
                  onPressed: () => _startConsultation(context, 'audio', audioRate),
                  icon: const Icon(Icons.call, size: 16),
                  label: Text('Call ₹${audioRate.toInt()}/m', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _startConsultation(BuildContext context, String type, double ratePerMin) {
    const double minMinutes = 5.0;
    final double minRequired = ratePerMin * minMinutes;

    if (UserState.walletBalance < minRequired) {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          backgroundColor: const Color(0xFF14172C),
          title: const Text('Insufficient Balance', style: TextStyle(color: Color(0xFFFFD700))),
          content: Text(
            'Minimum 5 minutes balance (₹${minRequired.toStringAsFixed(0)}) is required to initiate consultation.\n\nYour current balance: ₹${UserState.walletBalance.toStringAsFixed(0)}',
            style: const TextStyle(color: Colors.white70),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Cancel', style: TextStyle(color: Colors.white54)),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFFFD700), foregroundColor: Colors.black),
              onPressed: () {
                Navigator.pop(ctx);
                Navigator.push(context, MaterialPageRoute(builder: (_) => const WalletScreen()));
              },
              child: const Text('Recharge Now'),
            ),
          ],
        ),
      );
      return;
    }

    if (type == 'chat') {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => LiveChatConsultationScreen(
            astrologer: astrologer,
            ratePerMin: ratePerMin,
          ),
        ),
      );
    } else {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => LiveAgoraCallScreen(
            astrologer: astrologer,
            ratePerMin: ratePerMin,
            isVideo: type == 'video',
          ),
        ),
      );
    }
  }
}

// 3. LIVE AGORA CALL SCREEN WITH 60-SEC HEARTBEAT BILLING
class LiveAgoraCallScreen extends StatefulWidget {
  final Map<String, dynamic> astrologer;
  final double ratePerMin;
  final bool isVideo;

  const LiveAgoraCallScreen({
    super.key,
    required this.astrologer,
    required this.ratePerMin,
    required this.isVideo,
  });

  @override
  State<LiveAgoraCallScreen> createState() => _LiveAgoraCallScreenState();
}

class _LiveAgoraCallScreenState extends State<LiveAgoraCallScreen> {
  int _seconds = 0;
  Timer? _timer;
  Timer? _billingTimer;
  bool _isMuted = false;
  bool _isSpeaker = true;
  double _totalDeducted = 0.0;

  @override
  void initState() {
    super.initState();
    _startTimers();
  }

  void _startTimers() {
    // 1-second display timer
    _timer = Timer.periodic(const Duration(seconds: 1), (t) {
      setState(() => _seconds++);
    });

    // 60-second real-time wallet deduction heartbeat
    _billingTimer = Timer.periodic(const Duration(seconds: 60), (t) {
      if (UserState.walletBalance >= widget.ratePerMin) {
        setState(() {
          UserState.walletBalance -= widget.ratePerMin;
          _totalDeducted += widget.ratePerMin;
        });

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: const Color(0xFF1F2445),
            content: Text(
              '1 minute billed: -₹${widget.ratePerMin.toStringAsFixed(0)} | Balance: ₹${UserState.walletBalance.toStringAsFixed(0)}',
              style: const TextStyle(color: Color(0xFFFFD700)),
            ),
            duration: const Duration(seconds: 2),
          ),
        );
      } else {
        // Balance exhausted -> Auto-terminate call
        _endCall(exhausted: true);
      }
    });
  }

  void _endCall({bool exhausted = false}) {
    _timer?.cancel();
    _billingTimer?.cancel();

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF14172C),
        title: Text(exhausted ? 'Call Ended (Low Balance)' : 'Consultation Completed', style: const TextStyle(color: Color(0xFFFFD700))),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Duration: ${_seconds ~/ 60}m ${_seconds % 60}s', style: const TextStyle(color: Colors.white)),
            const SizedBox(height: 6),
            Text('Total Billed: ₹${_totalDeducted.toStringAsFixed(0)}', style: const TextStyle(color: Colors.white70)),
            const SizedBox(height: 6),
            Text('Remaining Balance: ₹${UserState.walletBalance.toStringAsFixed(0)}', style: const TextStyle(color: Colors.white70)),
          ],
        ),
        actions: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFFFD700), foregroundColor: Colors.black),
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pop(context);
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    _billingTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final String durationText = '${(_seconds ~/ 60).toString().padLeft(2, '0')}:${(_seconds % 60).toString().padLeft(2, '0')}';

    return Scaffold(
      backgroundColor: const Color(0xFF090A15),
      body: SafeArea(
        child: Column(
          children: [
            const SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.fiber_manual_record, color: Colors.red, size: 12),
                const SizedBox(width: 8),
                Text(
                  'Agora RTC Live Consultation • $durationText',
                  style: const TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold, fontSize: 14),
                ),
              ],
            ),
            const Spacer(),

            // Astrologer Avatar & Name
            CircleAvatar(
              radius: 65,
              backgroundImage: NetworkImage(widget.astrologer['image']),
            ),
            const SizedBox(height: 18),
            Text(
              widget.astrologer['name'],
              style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              '₹${widget.ratePerMin.toStringAsFixed(0)} / min • Connected via Agora RTC',
              style: const TextStyle(color: Colors.white54, fontSize: 13),
            ),
            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFF14172C),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFFFD700).withAlpha(102)),
              ),
              child: Text(
                'Wallet Balance: ₹${UserState.walletBalance.toStringAsFixed(0)}',
                style: const TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.w600, fontSize: 14),
              ),
            ),

            const Spacer(),

            // Action Buttons (Mute, End Call, Speaker)
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                IconButton(
                  iconSize: 32,
                  icon: Icon(_isMuted ? Icons.mic_off : Icons.mic, color: Colors.white),
                  style: IconButton.styleFrom(backgroundColor: const Color(0xFF1F2445), padding: const EdgeInsets.all(14)),
                  onPressed: () => setState(() => _isMuted = !_isMuted),
                ),
                IconButton(
                  iconSize: 38,
                  icon: const Icon(Icons.call_end, color: Colors.white),
                  style: IconButton.styleFrom(backgroundColor: Colors.red, padding: const EdgeInsets.all(18)),
                  onPressed: () => _endCall(),
                ),
                IconButton(
                  iconSize: 32,
                  icon: Icon(_isSpeaker ? Icons.volume_up : Icons.volume_down, color: Colors.white),
                  style: IconButton.styleFrom(backgroundColor: const Color(0xFF1F2445), padding: const EdgeInsets.all(14)),
                  onPressed: () => setState(() => _isSpeaker = !_isSpeaker),
                ),
              ],
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }
}

// 4. LIVE CHAT SCREEN
class LiveChatConsultationScreen extends StatefulWidget {
  final Map<String, dynamic> astrologer;
  final double ratePerMin;

  const LiveChatConsultationScreen({
    super.key,
    required this.astrologer,
    required this.ratePerMin,
  });

  @override
  State<LiveChatConsultationScreen> createState() => _LiveChatConsultationScreenState();
}

class _LiveChatConsultationScreenState extends State<LiveChatConsultationScreen> {
  final TextEditingController _msgController = TextEditingController();
  final List<Map<String, String>> _messages = [
    {
      'sender': 'astro',
      'text': 'Vanakkam! I am Guruji. Please share your Date, Time, and Place of Birth to begin the chart analysis.',
    }
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF101225),
        title: Row(
          children: [
            CircleAvatar(radius: 18, backgroundImage: NetworkImage(widget.astrologer['image'])),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.astrologer['name'], style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                  const Text('Online • Agora Chat', style: TextStyle(color: Colors.green, fontSize: 10)),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('End Chat', style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
      body: Column(
        children: [
          // Live Billing Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: const Color(0xFF14172C),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Rate: ₹${widget.ratePerMin.toInt()}/min', style: const TextStyle(color: Colors.white70, fontSize: 12)),
                Text('Balance: ₹${UserState.walletBalance.toStringAsFixed(0)}', style: const TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold, fontSize: 12)),
              ],
            ),
          ),
          // Chat Messages
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final msg = _messages[index];
                final isUser = msg['sender'] == 'user';
                return Align(
                  alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: isUser ? const Color(0xFFFFD700) : const Color(0xFF1F2445),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Text(
                      msg['text']!,
                      style: TextStyle(color: isUser ? Colors.black : Colors.white, fontSize: 14),
                    ),
                  ),
                );
              },
            ),
          ),
          // Input bar
          Container(
            padding: const EdgeInsets.all(10),
            color: const Color(0xFF101225),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _msgController,
                    style: const TextStyle(color: Colors.white),
                    decoration: InputDecoration(
                      hintText: 'Type your question or birth details...',
                      hintStyle: const TextStyle(color: Colors.white38),
                      filled: true,
                      fillColor: const Color(0xFF1A1D36),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  style: IconButton.styleFrom(backgroundColor: const Color(0xFFFFD700), foregroundColor: Colors.black),
                  icon: const Icon(Icons.send),
                  onPressed: () {
                    final text = _msgController.text.trim();
                    if (text.isNotEmpty) {
                      setState(() {
                        _messages.add({'sender': 'user', 'text': text});
                        _msgController.clear();
                      });
                      // Auto simulate astrologer reply
                      Future.delayed(const Duration(seconds: 1), () {
                        if (mounted) {
                          setState(() {
                            _messages.add({'sender': 'astro', 'text': 'Noted! Analyzing planetary transits in 10th & 7th house for auspicious outcome...'});
                          });
                        }
                      });
                    }
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// 5. DAILY KUNDLI / HOROSCOPE SCREEN
class DailyKundliScreen extends StatelessWidget {
  const DailyKundliScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Today Rasipalan & Kundli', style: TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF101225),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: const Color(0xFF14172C),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFFFD700).withAlpha(77)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text('🌟 Today Auspicious Timings (Suba Neram)', style: TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold, fontSize: 16)),
                  SizedBox(height: 10),
                  Text('• Nalla Neram: 09:15 AM - 10:15 AM', style: TextStyle(color: Colors.white70)),
                  Text('• Gowri Nalla Neram: 01:30 PM - 02:30 PM', style: TextStyle(color: Colors.white70)),
                  Text('• Rahu Kalam: 03:00 PM - 04:30 PM', style: TextStyle(color: Colors.white54)),
                  Text('• Kuligai: 12:00 PM - 01:30 PM', style: TextStyle(color: Colors.white54)),
                ],
              ),
            ),
            const SizedBox(height: 20),
            const Text('Daily Rasi Palan', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            _rasiItem('Mesham (Aries)', 'Financial gains through career efforts. Auspicious day for new decisions.'),
            _rasiItem('Rishabam (Taurus)', 'Family harmony prevails. Travel brings favorable outcomes.'),
            _rasiItem('Mithunam (Gemini)', 'Focus on health and wellness. Auspicious for spiritual remedies.'),
            _rasiItem('Kadagam (Cancer)', 'Good support from elders. Business partnerships prosper.'),
            _rasiItem('Simmam (Leo)', 'Leadership opportunities arise. Favorable time for marriage talks.'),
            _rasiItem('Kanni (Virgo)', 'Creative pursuits succeed. Divine blessings guide your investments.'),
          ],
        ),
      ),
    );
  }

  static Widget _rasiItem(String rasi, String forecast) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFF101225),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF1F2445)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(rasi, style: const TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold, fontSize: 14)),
          const SizedBox(height: 4),
          Text(forecast, style: const TextStyle(color: Colors.white70, fontSize: 12)),
        ],
      ),
    );
  }
}

// 6. WALLET & RECHARGE SCREEN
class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  final List<int> _amounts = [100, 200, 500, 1000, 2000];

  void _recharge(int amount) {
    setState(() {
      UserState.walletBalance += amount;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: Colors.green,
        content: Text('Recharge of ₹$amount Successful! New Balance: ₹${UserState.walletBalance.toStringAsFixed(0)}'),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Wallet', style: TextStyle(color: Color(0xFFFFD700), fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF101225),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Balance Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF281E48), Color(0xFF14172C)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFFFD700).withAlpha(102)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Available Balance', style: TextStyle(color: Colors.white70, fontSize: 14)),
                  const SizedBox(height: 8),
                  Text(
                    '₹${UserState.walletBalance.toStringAsFixed(2)}',
                    style: const TextStyle(color: Color(0xFFFFD700), fontSize: 32, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 10),
                  const Text('Valid for all Audio, Video, and Chat consultations', style: TextStyle(color: Colors.white54, fontSize: 11)),
                ],
              ),
            ),

            const SizedBox(height: 28),
            const Text('Instant Recharge Packs', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 14),

            // Packs Grid
            Wrap(
              spacing: 12,
              runSpacing: 12,
              children: _amounts.map((amt) {
                return ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF14172C),
                    foregroundColor: const Color(0xFFFFD700),
                    side: const BorderSide(color: Color(0xFF2D325A)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                  ),
                  onPressed: () => _recharge(amt),
                  child: Text('+ ₹$amt', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                );
              }).toList(),
            ),

            const Spacer(),

            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFFD700),
                foregroundColor: Colors.black,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
              ),
              onPressed: () => _recharge(500),
              child: const Text('Add ₹500 (Most Popular)', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
}
