<?php
// pages/dashboard/ai_assistant.php - AI Assistant Chatbot
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - Dashboard Kependudukan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard/ai_assistant.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Updated page header with proper contrast colors -->
        <div class="page-header" style="background: white; color: #2c3e50;">
            <h1 style="color: #2c3e50;"><i class="fas fa-robot"></i> AI Assistant</h1>
            <p style="color: #6c757d;">Asisten virtual untuk membantu analisis data kependudukan</p>
        </div>

        <div class="ai-container">
            <div class="chat-section">
                <div class="chat-header">
                    <div class="ai-status">
                        <div class="status-indicator online"></div>
                        <span>AI Assistant Online</span>
                    </div>
                    <!-- Removed clear chat button as requested -->
                </div>

                <div class="chat-messages" id="chatMessages">
                    <div class="message ai-message">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-text">
                                Halo! Saya AI Assistant untuk Dashboard Kependudukan. Saya dapat membantu Anda menganalisis data akta, demografi, disabilitas, kelompok umur, dan kepala keluarga. Silakan tanyakan apa yang ingin Anda ketahui!
                            </div>
                            <div class="message-time">
                                <?php echo date('H:i'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="typing-indicator" id="typingIndicator" style="display: none;">
                    <div class="message ai-message">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Updated popular questions to be more analytical for population data -->
                <div class="quick-questions">
                    <h4>Pertanyaan Populer:</h4>
                    <div class="question-buttons">
                        <button class="question-btn" data-question="Analisis tren kepemilikan akta cerai berdasarkan wilayah dengan persentase tertinggi dan terendah">
                            <i class="fas fa-chart-line"></i> Analisis Akta Cerai
                        </button>
                        <button class="question-btn" data-question="Bandingkan distribusi disabilitas fisik vs mental di 5 wilayah dengan populasi terbesar">
                            <i class="fas fa-balance-scale"></i> Komparasi Disabilitas
                        </button>
                        <button class="question-btn" data-question="Identifikasi korelasi antara jumlah kepala keluarga perempuan dengan tingkat ekonomi wilayah">
                            <i class="fas fa-search-plus"></i> Korelasi Gender-Ekonomi
                        </button>
                        <button class="question-btn" data-question="Proyeksi kebutuhan fasilitas lansia berdasarkan data kelompok umur 60+ tahun per wilayah">
                            <i class="fas fa-chart-area"></i> Proyeksi Lansia
                        </button>
                        <button class="question-btn" data-question="Analisis rasio dependency ratio dan dampaknya terhadap perencanaan pembangunan daerah">
                            <i class="fas fa-calculator"></i> Dependency Ratio
                        </button>
                        <button class="question-btn" data-question="Evaluasi efektivitas program disabilitas berdasarkan sebaran geografis dan jenis disabilitas">
                            <i class="fas fa-map-marked-alt"></i> Evaluasi Program
                        </button>
                    </div>
                </div>

                <div class="chat-input-container">
                    <div class="chat-input-wrapper">
                        <input type="text" id="chatInput" placeholder="Ketik pertanyaan Anda..." maxlength="500">
                        <button id="sendMessage" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="input-counter">
                        <span id="charCounter">0</span>/500
                    </div>
                </div>
            </div>

            <!-- Updated history panel with rename functionality and removed export/clear buttons -->
            <div class="history-panel">
                <div class="panel-header">
                    <h3><i class="fas fa-history"></i> Chat History</h3>
                    <button class="new-chat-btn" id="newChatBtn">
                        <i class="fas fa-plus"></i> New Chat
                    </button>
                </div>
                
                <div class="chat-rooms" id="chatRooms">
                    <div class="room-item active" data-room-id="default">
                        <div class="room-info">
                            <div class="room-title editable" data-room-id="default">Chat Utama</div>
                            <div class="room-preview">Halo! Saya AI Assistant...</div>
                            <div class="room-time"><?php echo date('H:i'); ?></div>
                        </div>
                        <div class="room-actions">
                            <button class="rename-room-btn" data-room-id="default" title="Rename">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-room-btn" data-room-id="default" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/dashboard/ai_assistant.js"></script>
</body>
</html>
