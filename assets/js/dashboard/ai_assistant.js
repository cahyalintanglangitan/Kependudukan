// AI Assistant JavaScript with Chat History Management
class AIAssistant {
  constructor() {
    this.chatMessages = document.getElementById("chatMessages")
    this.chatInput = document.getElementById("chatInput")
    this.sendButton = document.getElementById("sendMessage")
    this.typingIndicator = document.getElementById("typingIndicator")
    this.charCounter = document.getElementById("charCounter")
    this.chatRooms = document.getElementById("chatRooms")
    this.newChatBtn = document.getElementById("newChatBtn")

    this.currentRoomId = "default"
    this.chatHistory = this.loadAllChatHistory()

    this.initializeEventListeners()
    this.loadChatRoom(this.currentRoomId)
    this.renderChatRooms()
  }

  initializeEventListeners() {
    // Send message events
    this.sendButton.addEventListener("click", () => this.sendMessage())
    this.chatInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault()
        this.sendMessage()
      }
    })

    // Character counter
    this.chatInput.addEventListener("input", () => {
      const length = this.chatInput.value.length
      this.charCounter.textContent = length

      if (length > 450) {
        this.charCounter.style.color = "#e74c3c"
      } else if (length > 400) {
        this.charCounter.style.color = "#f39c12"
      } else {
        this.charCounter.style.color = "#95a5a6"
      }
    })

    // Quick question buttons
    document.querySelectorAll(".question-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        const question = btn.getAttribute("data-question")
        this.chatInput.value = question
        this.sendMessage()
      })
    })

    this.newChatBtn.addEventListener("click", () => this.createNewChatRoom())
  }

  createNewChatRoom() {
    const roomId = "chat_" + Date.now()
    const roomTitle = `Chat ${Object.keys(this.chatHistory).length + 1}`

    this.chatHistory[roomId] = {
      title: roomTitle,
      messages: [
        {
          sender: "ai",
          text: "Halo! Saya AI Assistant untuk Dashboard Kependudukan. Saya dapat membantu Anda menganalisis data akta, demografi, disabilitas, kelompok umur, dan kepala keluarga. Silakan tanyakan apa yang ingin Anda ketahui!",
          time: new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }),
          timestamp: Date.now(),
        },
      ],
      lastActivity: Date.now(),
    }

    this.switchToChatRoom(roomId)
    this.saveAllChatHistory()
    this.renderChatRooms()
  }

  switchToChatRoom(roomId) {
    this.currentRoomId = roomId
    this.loadChatRoom(roomId)
    this.renderChatRooms()
  }

  loadChatRoom(roomId) {
    // Clear current messages
    this.chatMessages.innerHTML = ""

    if (this.chatHistory[roomId] && this.chatHistory[roomId].messages) {
      this.chatHistory[roomId].messages.forEach((msg) => {
        this.addMessageFromHistory(msg.text, msg.sender, msg.time)
      })
    }
  }

  renderChatRooms() {
    this.chatRooms.innerHTML = ""

    // Sort rooms by last activity
    const sortedRooms = Object.entries(this.chatHistory).sort(([, a], [, b]) => b.lastActivity - a.lastActivity)

    sortedRooms.forEach(([roomId, roomData]) => {
      const roomElement = document.createElement("div")
      roomElement.className = `room-item ${roomId === this.currentRoomId ? "active" : ""}`
      roomElement.setAttribute("data-room-id", roomId)

      const lastMessage = roomData.messages[roomData.messages.length - 1]
      const preview = lastMessage ? lastMessage.text.substring(0, 50) + "..." : "Tidak ada pesan"
      const timeStr = new Date(roomData.lastActivity).toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
      })

      roomElement.innerHTML = `
        <div class="room-info">
          <div class="room-title editable" data-room-id="${roomId}">${roomData.title}</div>
          <div class="room-preview">${preview}</div>
          <div class="room-time">${timeStr}</div>
        </div>
        <div class="room-actions">
          <button class="rename-room-btn" data-room-id="${roomId}" title="Rename">
            <i class="fas fa-edit"></i>
          </button>
          <button class="delete-room-btn" data-room-id="${roomId}" title="Delete">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      `

      // Room click event
      roomElement.addEventListener("click", (e) => {
        if (!e.target.closest(".room-actions")) {
          this.switchToChatRoom(roomId)
        }
      })

      const renameBtn = roomElement.querySelector(".rename-room-btn")
      renameBtn.addEventListener("click", (e) => {
        e.stopPropagation()
        this.startRenameRoom(roomId)
      })

      // Delete room event
      const deleteBtn = roomElement.querySelector(".delete-room-btn")
      deleteBtn.addEventListener("click", (e) => {
        e.stopPropagation()
        this.deleteChatRoom(roomId)
      })

      this.chatRooms.appendChild(roomElement)
    })
  }

  startRenameRoom(roomId) {
    const roomTitleElement = document.querySelector(`[data-room-id="${roomId}"] .room-title`)
    const currentTitle = roomTitleElement.textContent

    // Create input element
    const input = document.createElement("input")
    input.type = "text"
    input.value = currentTitle
    input.className = "room-title editing"
    input.maxLength = 50

    // Replace title with input
    roomTitleElement.replaceWith(input)
    input.focus()
    input.select()

    const finishRename = () => {
      const newTitle = input.value.trim() || currentTitle
      this.chatHistory[roomId].title = newTitle
      this.saveAllChatHistory()
      this.renderChatRooms()
    }

    input.addEventListener("blur", finishRename)
    input.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        finishRename()
      }
    })
  }

  deleteChatRoom(roomId) {
    if (Object.keys(this.chatHistory).length <= 1) {
      alert("Tidak dapat menghapus chat terakhir!")
      return
    }

    if (confirm("Apakah Anda yakin ingin menghapus chat room ini?")) {
      delete this.chatHistory[roomId]

      if (this.currentRoomId === roomId) {
        // Switch to first available room
        this.currentRoomId = Object.keys(this.chatHistory)[0]
        this.loadChatRoom(this.currentRoomId)
      }

      this.saveAllChatHistory()
      this.renderChatRooms()
    }
  }

  sendMessage() {
    const message = this.chatInput.value.trim()
    if (!message) return

    // Add user message
    this.addMessage(message, "user")
    this.chatInput.value = ""
    this.charCounter.textContent = "0"
    this.charCounter.style.color = "#95a5a6"

    // Show typing indicator
    this.showTypingIndicator()

    // Simulate AI response delay
    setTimeout(
      () => {
        this.hideTypingIndicator()
        const response = this.generateAIResponse(message)
        this.addMessage(response, "ai")
      },
      1000 + Math.random() * 2000,
    )
  }

  addMessage(text, sender) {
    const messageDiv = document.createElement("div")
    messageDiv.className = `message ${sender}-message`

    const currentTime = new Date().toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    })

    messageDiv.innerHTML = `
      <div class="message-avatar">
        <i class="fas ${sender === "user" ? "fa-user" : "fa-robot"}"></i>
      </div>
      <div class="message-content">
        <div class="message-text">${text}</div>
        <div class="message-time">${currentTime}</div>
      </div>
    `

    this.chatMessages.appendChild(messageDiv)
    this.scrollToBottom()

    this.saveMessageToCurrentRoom(text, sender, currentTime)
  }

  saveMessageToCurrentRoom(text, sender, time) {
    if (!this.chatHistory[this.currentRoomId]) {
      this.chatHistory[this.currentRoomId] = {
        title: `Chat ${Object.keys(this.chatHistory).length + 1}`,
        messages: [],
        lastActivity: Date.now(),
      }
    }

    this.chatHistory[this.currentRoomId].messages.push({
      sender,
      text,
      time,
      timestamp: Date.now(),
    })

    this.chatHistory[this.currentRoomId].lastActivity = Date.now()

    // Update room title based on first user message
    if (
      sender === "user" &&
      this.chatHistory[this.currentRoomId].messages.filter((m) => m.sender === "user").length === 1
    ) {
      this.chatHistory[this.currentRoomId].title = text.substring(0, 30) + (text.length > 30 ? "..." : "")
    }

    this.saveAllChatHistory()
    this.renderChatRooms()
  }

  generateAIResponse(userMessage) {
    const message = userMessage.toLowerCase()

    // Enhanced analytical responses for population data
    if (message.includes("analisis") && message.includes("akta cerai")) {
      return "Analisis Akta Cerai Komprehensif:\n\nWilayah Tertinggi: Daerah urban menunjukkan tingkat perceraian 15-20% lebih tinggi\nFaktor Korelasi: Tingkat pendidikan dan ekonomi berpengaruh signifikan\nTren Temporal: Peningkatan 8% dalam 3 tahun terakhir\nRekomendasi: Perlunya program konseling keluarga di area high-risk\n\nApakah Anda ingin drill-down ke wilayah spesifik?"
    }

    if (message.includes("bandingkan") && message.includes("disabilitas")) {
      return "Komparasi Disabilitas Fisik vs Mental:\n\nDisabilitas Fisik: 128.428 kasus (22.1% total)\nDisabilitas Mental: 224.768 kasus (38.8% total)\nRasio: 1:1.75 (Mental lebih dominan)\nDistribusi Geografis: Mental tinggi di urban, fisik di rural\nImplikasi: Kebutuhan layanan psikososial lebih mendesak\n\nPerlu analisis lebih detail per kabupaten/kota?"
    }

    if (message.includes("korelasi") && message.includes("gender")) {
      return "Analisis Korelasi Gender-Ekonomi:\n\nKepala Keluarga Perempuan: 37.999.780 (20.3%)\nKorelasi Ekonomi: Wilayah dengan KK perempuan tinggi = ekonomi menengah ke bawah\nFaktor Pendorong: Migrasi laki-laki, perceraian, kemandirian ekonomi\nDampak Sosial: Perubahan pola konsumsi dan investasi pendidikan\n\nIngin melihat breakdown per sektor ekonomi?"
    }

    if (message.includes("proyeksi") && message.includes("lansia")) {
      return "Proyeksi Kebutuhan Fasilitas Lansia:\n\nPopulasi 60+: 64.808.150 jiwa (11.4% total)\nProyeksi 2030: Peningkatan 40-50% (aging population)\nKebutuhan Fasilitas: 1 puskesmas lansia per 5.000 lansia\nGap Saat Ini: Defisit 60% fasilitas kesehatan khusus lansia\nPrioritas Wilayah: Jawa Tengah, Jawa Timur, DIY\n\nPerlu simulasi skenario investasi?"
    }

    if (message.includes("dependency ratio")) {
      return "Analisis Dependency Ratio:\n\nRatio Saat Ini: 45.2 (per 100 usia produktif)\nKomposisi: 35.1 anak + 10.1 lansia\nTren: Shifting dari child ke elderly dependency\nImplikasi Ekonomi: Beban fiskal meningkat 25% dalam 10 tahun\nStrategi: Optimalisasi bonus demografis sebelum 2035\n\nIngin analisis per provinsi?"
    }

    if (message.includes("evaluasi program")) {
      return "Evaluasi Program Disabilitas:\n\nCoverage Rate: 65% penyandang disabilitas terjangkau program\nEfektivitas Geografis: Urban 80%, rural 45%\nGap Layanan: Disabilitas mental kurang mendapat perhatian\nROI Program: Setiap Rp 1M investasi = Rp 2.3M benefit ekonomi\nRekomendasi: Fokus pada mobile services untuk rural area\n\nPerlu detail program spesifik?"
    }

    if (message.includes("akta")) {
      if (message.includes("lahir")) {
        return "Analisis Akta Lahir Mendalam:\n\nBerdasarkan data komprehensif, tingkat kepemilikan akta lahir menunjukkan variasi signifikan antar wilayah (62-95%). Faktor utama: aksesibilitas layanan, tingkat pendidikan, dan kesadaran hukum. Rekomendasi: program mobile registration dan digitalisasi layanan.\n\nInsight Kunci: Korelasi positif antara kepemilikan akta dengan akses pendidikan formal."
      } else if (message.includes("mati")) {
        return "Analisis Akta Kematian:\n\nPola registrasi kematian mencerminkan sistem surveilans demografis. Data menunjukkan under-reporting 15-25% terutama di daerah terpencil. Implikasi: bias dalam perencanaan kesehatan masyarakat dan alokasi sumber daya.\n\nRekomendasi: Integrasi dengan sistem kesehatan dan kerjasama dengan tokoh agama."
      } else {
        return "Overview Sistem Akta Kependudukan:\n\nSistem registrasi vital menunjukkan progress signifikan namun masih ada gap. Akta lahir: 78% coverage, akta mati: 65%, akta cerai: 85%. Digitalisasi meningkatkan efisiensi 40%.\n\nStrategic Focus: Standardisasi sistem, capacity building, dan public awareness campaign."
      }
    }

    if (message.includes("disabilitas")) {
      return "Deep Dive Analisis Disabilitas:\n\nData 579.671 penyandang disabilitas menunjukkan kompleksitas kebutuhan layanan. Distribusi: Mental (38.8%), Fisik (22.1%), Rungu/Wicara (15.7%). Tantangan utama: stigma sosial dan aksesibilitas.\n\nPolicy Insight: Investasi infrastruktur inklusif memberikan multiplier effect pada ekonomi lokal."
    }

    if (message.includes("demografi")) {
      return "Analisis Demografis Strategis:\n\nKomposisi penduduk 284.973.643 jiwa dengan sex ratio 101.9 menunjukkan stabilitas demografis. Window of opportunity: bonus demografis hingga 2035. Tantangan: urbanisasi 4.2% annually dan aging population.\n\nStrategic Implication: Perlu rebalancing investasi infrastruktur urban-rural."
    }

    if (message.includes("kelompok umur")) {
      return "Analisis Struktur Usia Populasi:\n\nPiramida penduduk menunjukkan transisi demografis. Usia produktif (15-64): 68.2% populasi. Dependency ratio: 46.7 per 100 usia produktif. Critical point: 2030-2035 peak productive age.\n\nPolicy Window: Maksimalkan human capital investment sekarang untuk sustainable growth."
    }

    if (message.includes("kepala keluarga")) {
      return "Analisis Dinamika Kepala Keluarga:\n\nTren kepemimpinan keluarga berubah: KK perempuan meningkat 12% dalam 5 tahun. Faktor: emansipasi, ekonomi, dan perubahan sosial. Implikasi: pola konsumsi, investasi pendidikan, dan decision making berbeda.\n\nEconomic Impact: Household dengan KK perempuan cenderung prioritaskan pendidikan dan kesehatan."
    }

    if (message.includes("halo") || message.includes("hai") || message.includes("hello")) {
      return "Halo! Senang bisa membantu Anda menganalisis data kependudukan. Saya dapat memberikan insight tentang akta, demografi, disabilitas, kelompok umur, dan kepala keluarga. Silakan tanyakan hal spesifik yang ingin Anda ketahui!"
    }

    if (message.includes("terima kasih") || message.includes("thanks") || message.includes("makasih")) {
      return "Sama-sama! Saya senang bisa membantu analisis data kependudukan Anda. Jangan ragu untuk bertanya lagi jika membutuhkan insight atau analisis lebih lanjut. Saya siap membantu kapan saja!"
    }

    // Default response
    return "AI Assistant Siap Membantu!\n\nSaya dapat memberikan analisis mendalam untuk:\n\nAkta: Distribusi, gap coverage, efektivitas program\nDemografi: Bonus demografis, migrasi, proyeksi\nDisabilitas: Mapping kebutuhan, evaluasi program\nKelompok Umur: Dependency ratio, workforce planning\nKepala Keluarga: Gender dynamics, economic impact\n\nContoh pertanyaan: 'Analisis korelasi pendidikan dengan kepemilikan akta' atau 'Proyeksi kebutuhan layanan lansia 2030'"
  }

  showTypingIndicator() {
    this.typingIndicator.style.display = "block"
    this.scrollToBottom()
  }

  hideTypingIndicator() {
    this.typingIndicator.style.display = "none"
  }

  scrollToBottom() {
    setTimeout(() => {
      this.chatMessages.scrollTop = this.chatMessages.scrollHeight
    }, 100)
  }

  addMessageFromHistory(text, sender, time) {
    const messageDiv = document.createElement("div")
    messageDiv.className = `message ${sender}-message`

    messageDiv.innerHTML = `
      <div class="message-avatar">
        <i class="fas ${sender === "user" ? "fa-user" : "fa-robot"}"></i>
      </div>
      <div class="message-content">
        <div class="message-text">${text}</div>
        <div class="message-time">${time}</div>
      </div>
    `

    this.chatMessages.appendChild(messageDiv)
  }

  loadAllChatHistory() {
    const history = localStorage.getItem("ai_chat_rooms")
    if (history) {
      return JSON.parse(history)
    } else {
      // Create default room
      return {
        default: {
          title: "Chat Utama",
          messages: [
            {
              sender: "ai",
              text: "Halo! Saya AI Assistant untuk Dashboard Kependudukan. Saya dapat membantu Anda menganalisis data akta, demografi, disabilitas, kelompok umur, dan kepala keluarga. Silakan tanyakan apa yang ingin Anda ketahui!",
              time: new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }),
              timestamp: Date.now(),
            },
          ],
          lastActivity: Date.now(),
        },
      }
    }
  }

  saveAllChatHistory() {
    localStorage.setItem("ai_chat_rooms", JSON.stringify(this.chatHistory))
  }
}

// Initialize AI Assistant when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.aiAssistant = new AIAssistant()
})
