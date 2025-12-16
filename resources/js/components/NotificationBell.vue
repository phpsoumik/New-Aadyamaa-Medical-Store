<!-- @format -->

<template>
  <div class="notification-bell">
    <Button
      icon="pi pi-bell"
      class="p-button-rounded p-button-text p-button-sm"
      :badge="unreadCount > 0 ? unreadCount.toString() : null"
      badgeClass="p-badge-danger"
      @click="togglePanel"
    />

    <OverlayPanel ref="notificationPanel" style="width: 330px">
      <div class="notification-header">
        <h5>Notifications</h5>
        <Button
          v-if="notifications.length > 0"
          label="Mark All Read"
          class="p-button-text p-button-sm"
          @click="markAllAsRead"
        />
      </div>

      <div
        class="notification-list"
        style="max-height: 400px; overflow-y: auto"
      >
        <div
          v-if="notifications.length === 0"
          class="p-text-center p-p-3"
          style="color: #999"
        >
          No new notifications
        </div>

        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="notification-item"
          :class="{ unread: notification.status === 'unread' }"
          @click="markAsRead(notification.id)"
        >
          <div class="notification-icon">
            <i
              class="pi pi-check-circle"
              style="color: #4caf50; font-size: 1.5rem"
            ></i>
          </div>
          <div class="notification-content">
            <div class="notification-title">
              <b>{{ notification.medicine_name }}</b> is available!
            </div>
            <div class="notification-customer">
              <i class="pi pi-user"></i> {{ notification.customer_name }}
            </div>
            <div class="notification-phone">
              <i class="pi pi-phone"></i>
              <a :href="'tel:' + notification.customer_phone">{{
                notification.customer_phone
              }}</a>
            </div>
            <div class="notification-time">
              {{ formatTime(notification.created_at) }}
            </div>
          </div>
        </div>
      </div>
    </OverlayPanel>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import axios from "axios";

@Options({})
export default class NotificationBell extends Vue {
  private notifications = [];
  private unreadCount = 0;
  private intervalId = null;

  mounted() {
    this.fetchNotifications();
    // Auto refresh every 30 seconds
    this.intervalId = setInterval(() => {
      this.fetchNotifications();
    }, 30000);
  }

  beforeUnmount() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }
  }

  fetchNotifications() {
    axios
      .get("/api/notifications")
      .then((response) => {
        if (response.data.success) {
          this.notifications = response.data.notifications;
          this.unreadCount = response.data.count;
        }
      })
      .catch((error) => {
        console.error("Error fetching notifications:", error);
      });
  }

  togglePanel(event) {
    this.$refs.notificationPanel.toggle(event);
  }

  markAsRead(id) {
    axios
      .post("/api/notifications/mark-read", { id })
      .then(() => {
        this.fetchNotifications();
      })
      .catch((error) => {
        console.error("Error marking notification as read:", error);
      });
  }

  markAllAsRead() {
    axios
      .post("/api/notifications/mark-all-read")
      .then(() => {
        this.fetchNotifications();
      })
      .catch((error) => {
        console.error("Error marking all as read:", error);
      });
  }

  formatTime(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds

    if (diff < 60) return "Just now";
    if (diff < 3600) return Math.floor(diff / 60) + " minutes ago";
    if (diff < 86400) return Math.floor(diff / 3600) + " hours ago";
    return Math.floor(diff / 86400) + " days ago";
  }
}
</script>

<style scoped>
.notification-bell {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.notification-bell .p-button {
  color: #000 !important;
  width: 2rem;
  height: 2rem;
  padding: 0.4rem !important;
}

.notification-bell .p-button .pi {
  font-size: 1rem;
}

.notification-bell .p-button:hover {
  background-color: rgba(0, 0, 0, 0.04) !important;
}

.notification-bell .p-badge {
  min-width: 1rem;
  height: 1rem;
  line-height: 1rem;
  font-size: 0.6rem;
  top: 0.2rem;
  right: 0.2rem;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 10px;
  border-bottom: 1px solid #ddd;
  margin-bottom: 10px;
}

.notification-header h4 {
  margin: 0;
}

.notification-item {
  display: flex;
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: #f5f5f5;
}

.notification-item.unread {
  background-color: #e3f2fd;
}

.notification-icon {
  margin-right: 12px;
  flex-shrink: 0;
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-size: 14px;
  margin-bottom: 5px;
}

.notification-customer,
.notification-phone {
  font-size: 13px;
  color: #666;
  margin-bottom: 3px;
}

.notification-phone a {
  color: #1976d2;
  text-decoration: none;
}

.notification-phone a:hover {
  text-decoration: underline;
}

.notification-time {
  font-size: 11px;
  color: #999;
  margin-top: 5px;
}
</style>
