// Main JavaScript file for SecondLife Market

document.addEventListener("DOMContentLoaded", () => {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl))

  // Image preview for product upload
  const imageInput = document.getElementById("image")
  const imagePreview = document.getElementById("imagePreview")

  if (imageInput && imagePreview) {
    imageInput.addEventListener("change", function () {
      const file = this.files[0]
      if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
          imagePreview.src = e.target.result
          imagePreview.style.display = "block"
        }
        reader.readAsDataURL(file)
      }
    })
  }
})

// Chat functionality
document.addEventListener("DOMContentLoaded", () => {
  const chatForm = document.getElementById("chatForm")
  if (chatForm) {
    chatForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const messageInput = document.getElementById("message")
      const message = messageInput.value.trim()
      const receiverId = this.dataset.receiverId
      const productId = this.dataset.productId || null

      if (message === "") return

      // Add message to chat (optimistic UI update)
      const chatMessages = document.getElementById("chatMessages")
      const messageElement = document.createElement("div")
      messageElement.className = "chat-message chat-message-sent"
      messageElement.innerHTML = `
                <div class="message-content">${message.replace(/\n/g, "<br>")}</div>
                <div class="message-time small">${new Date().getHours().toString().padStart(2, "0")}:${new Date().getMinutes().toString().padStart(2, "0")}</div>
            `
      chatMessages.appendChild(messageElement)
      chatMessages.scrollTop = chatMessages.scrollHeight

      // Clear input
      messageInput.value = ""

      // Send message to server
      const formData = new FormData()
      formData.append("receiver_id", receiverId)
      formData.append("message", message)
      if (productId) {
        formData.append("product_id", productId)
      }

      fetch("ajax/send-message.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (!data.success) {
            console.error("Error sending message:", data.message)
            // You could add error handling UI here
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    })

    // Auto-resize textarea
    const messageInput = document.getElementById("message")
    if (messageInput) {
      messageInput.addEventListener("input", function () {
        this.style.height = "auto"
        this.style.height = this.scrollHeight + "px"
      })
    }

    // Poll for new messages if in a chat
    const receiverId = chatForm.dataset.receiverId
    if (receiverId) {
      function getLastMessageId() {
        const messages = document.querySelectorAll(".chat-message")
        if (messages.length > 0) {
          const lastMessage = messages[messages.length - 1]
          return lastMessage.dataset.id || 0
        }
        return 0
      }

      function pollMessages() {
        const lastId = getLastMessageId()
        fetch(`ajax/get-messages.php?receiver_id=${receiverId}&last_id=${lastId}`)
          .then((response) => response.json())
          .then((data) => {
            if (data.success && data.messages.length > 0) {
              const chatMessages = document.getElementById("chatMessages")
              let lastDate = null

              data.messages.forEach((message) => {
                // Check if we need to add a date separator
                const messageDate = new Date(message.date)
                const messageDateStr = messageDate.toISOString().split("T")[0]

                if (lastDate !== messageDateStr) {
                  lastDate = messageDateStr
                  const dateElement = document.createElement("div")
                  dateElement.className = "text-center my-3"
                  dateElement.innerHTML = `<span class="badge bg-light text-dark">${messageDate.toLocaleDateString()}</span>`
                  chatMessages.appendChild(dateElement)
                }

                // Add the message
                const messageElement = document.createElement("div")
                messageElement.className = `chat-message ${message.is_sent ? "chat-message-sent" : "chat-message-received"}`
                messageElement.dataset.id = message.id

                let productHtml = ""
                if (message.product_id) {
                  productHtml = `
                                    <div class="card mb-2" style="max-width: 200px;">
                                        <img src="uploads/products/${message.product_image}" class="card-img-top" alt="${message.product_name}" style="height: 100px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <p class="card-text small">${message.product_name}</p>
                                            <a href="product-detail.php?id=${message.product_id}" class="btn btn-sm btn-primary w-100">Xem sản phẩm</a>
                                        </div>
                                    </div>
                                `
                }

                messageElement.innerHTML = `
                                ${productHtml}
                                <div class="message-content">${message.message.replace(/\n/g, "<br>")}</div>
                                <div class="message-time small">${message.time}</div>
                            `
                chatMessages.appendChild(messageElement)
              })

              chatMessages.scrollTop = chatMessages.scrollHeight
            }
          })
          .catch((error) => {
            console.error("Error polling messages:", error)
          })
      }

      // Poll every 5 seconds
      setInterval(pollMessages, 5000)
    }
  }

  // Report product functionality
  const reportForm = document.getElementById("reportForm")
  if (reportForm) {
    reportForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const reason = document.getElementById("reportReason").value
      const productId = this.dataset.productId

      if (reason.trim() === "") {
        alert("Vui lòng nhập lý do báo cáo")
        return
      }

      const formData = new FormData()
      formData.append("product_id", productId)
      formData.append("reason", reason)

      fetch("ajax/report-product.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Báo cáo đã được gửi. Cảm ơn bạn đã giúp chúng tôi duy trì cộng đồng an toàn!")
            document.getElementById("reportReason").value = ""
            const reportModal = bootstrap.Modal.getInstance(document.getElementById("reportModal"))
            reportModal.hide()
          } else {
            alert(data.message)
            if (data.message === "Bạn cần đăng nhập để thực hiện chức năng này") {
              window.location.href = "login.php"
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    })
  }

  // Auto-resize textareas
  const textareas = document.querySelectorAll("textarea.auto-resize")
  textareas.forEach((textarea) => {
    textarea.addEventListener("input", function () {
      this.style.height = "auto"
      this.style.height = this.scrollHeight + "px"
    })

    // Initial resize
    textarea.style.height = "auto"
    textarea.style.height = textarea.scrollHeight + "px"
  })
})

// Enhanced script with better error handling and debugging
$(document).ready(() => {
  console.log("Script loaded")

  // Favorite toggle functionality - Fixed version
  $(document).on("click", ".favorite-btn", function (e) {
    e.preventDefault()
    console.log("Favorite button clicked")

    var button = $(this)
    var productId = button.data("product-id")

    console.log("Product ID:", productId)

    if (!productId) {
      console.error("No product ID found")
      alert("Lỗi: Không tìm thấy ID sản phẩm")
      return
    }

    // Disable button during request
    button.prop("disabled", true)

    $.ajax({
      url: "ajax/toggle-favorite.php",
      type: "POST",
      data: {
        product_id: productId,
      },
      dataType: "json",
      beforeSend: () => {
        console.log("Sending AJAX request...")
      },
      success: (response) => {
        console.log("AJAX Success:", response)

        if (response.success) {
          if (response.action === "added") {
            button.html('<i class="fas fa-heart text-danger"></i> Bỏ thích')
            button.removeClass("btn-outline-danger").addClass("btn-danger")
          } else {
            button.html('<i class="far fa-heart"></i> Yêu thích')
            button.removeClass("btn-danger").addClass("btn-outline-danger")

            // If on favorites page, remove the card
            if (window.location.pathname.includes("favorites.php")) {
              button.closest(".col-md-6").fadeOut()
            }
          }

          // Show success message
          showMessage(response.message, "success")
        } else {
          console.error("Server error:", response.message)
          showMessage(response.message, "error")
        }
      },
      error: (xhr, status, error) => {
        console.error("AJAX Error:", error)
        console.error("Status:", status)
        console.error("Response:", xhr.responseText)

        if (xhr.status === 0) {
          showMessage("Lỗi kết nối. Vui lòng kiểm tra internet.", "error")
        } else if (xhr.status === 404) {
          showMessage("Không tìm thấy trang xử lý yêu cầu.", "error")
        } else if (xhr.status === 500) {
          showMessage("Lỗi server. Vui lòng thử lại sau.", "error")
        } else {
          showMessage("Đã xảy ra lỗi: " + error, "error")
        }
      },
      complete: () => {
        // Re-enable button
        button.prop("disabled", false)
      },
    })
  })

  // Message display function
  function showMessage(message, type) {
    var alertClass = type === "success" ? "alert-success" : "alert-danger"
    var alertHtml =
      '<div class="alert ' +
      alertClass +
      ' alert-dismissible fade show" role="alert">' +
      message +
      '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
      "</div>"

    // Remove existing alerts
    $(".alert").remove()

    // Add new alert at top of page
    $("body").prepend(alertHtml)

    // Auto hide after 3 seconds
    setTimeout(() => {
      $(".alert").fadeOut()
    }, 3000)
  }

  // Report product functionality
  $(document).on("click", ".report-btn", function (e) {
    e.preventDefault()

    var productId = $(this).data("product-id")
    var reason = prompt(
      "Lý do báo cáo:\n1. Sản phẩm giả\n2. Nội dung không phù hợp\n3. Lừa đảo\n4. Khác\n\nNhập số (1-4):",
    )

    if (reason) {
      var reasons = ["", "fake", "inappropriate", "scam", "other"]
      var selectedReason = reasons[Number.parseInt(reason)] || "other"

      $.ajax({
        url: "ajax/report-product.php",
        type: "POST",
        data: {
          product_id: productId,
          reason: selectedReason,
        },
        dataType: "json",
        success: (response) => {
          if (response.success) {
            showMessage(response.message, "success")
          } else {
            showMessage(response.message, "error")
          }
        },
        error: () => {
          showMessage("Đã xảy ra lỗi khi gửi báo cáo.", "error")
        },
      })
    }
  })

  // Auto-refresh messages every 30 seconds on chat page
  if (window.location.pathname.includes("chat.php")) {
    setInterval(() => {
      loadMessages()
    }, 30000)
  }

  // Load messages function for chat
  function loadMessages() {
    var receiverId = $("#receiver_id").val()
    if (receiverId) {
      $.ajax({
        url: "ajax/get-messages.php",
        type: "GET",
        data: { receiver_id: receiverId },
        success: (response) => {
          $("#messages-container").html(response)
          // Scroll to bottom
          $("#messages-container").scrollTop($("#messages-container")[0].scrollHeight)
        },
      })
    }
  }

  // Send message functionality
  $("#send-message-form").on("submit", function (e) {
    e.preventDefault()

    var formData = $(this).serialize()
    var messageInput = $("#message")

    $.ajax({
      url: "ajax/send-message.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: (response) => {
        if (response.success) {
          messageInput.val("")
          loadMessages()
        } else {
          showMessage(response.message, "error")
        }
      },
      error: () => {
        showMessage("Đã xảy ra lỗi khi gửi tin nhắn.", "error")
      },
    })
  })
})
