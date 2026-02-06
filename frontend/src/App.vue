<template>
  <div class="wrap">
    <header class="top">
      <button class="logo" type="button" @click="goPosts" aria-label="게시글 목록으로 이동">
        Simple CMS Admin System
      </button>

      <div class="right">
        <span v-if="auth.user" class="user">
          {{ auth.user.name }} ({{ auth.user.role }})
        </span>

        <button v-if="auth.user" class="header-btn" @click="onLogout">로그아웃</button>

        <button
          v-if="showAdminToggle"
          class="header-btn"
          @click="onAdminToggle"
        >
          {{ adminToggleText }}
        </button>
      </div>
    </header>

    <main class="main">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { onMounted, computed } from "vue";
import { useAuthStore } from "./stores/auth";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const isAdminPage = computed(() => route.path.startsWith("/admin"));
const showAdminToggle = computed(() => !!auth.user && auth.isAdmin);
const adminToggleText = computed(() =>
  isAdminPage.value ? "게시글 목록으로 이동" : "관리자페이지로 이동"
);

function goPosts() {
  router.push("/posts");
}

async function onLogout() {
  await auth.logout();
  router.replace("/login");
}

function onAdminToggle() {
  if (isAdminPage.value) router.push("/posts");
  else router.push("/admin");
}
</script>

<style lang="scss" scoped>
.wrap {
  font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  min-height: 100vh;
  background-color: #f3f4f6;
  color: #1f2937;
}

.top {
  background-color: #ffffff;
  height: 64px;
  padding: 0 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* 로고를 strong 대신 버튼처럼(접근성+클릭) */
.logo {
  font-weight: 700;
  font-size: 16px;
  border: none;
  background: transparent;  
  cursor: pointer;
  padding: 0;
  color: #4f46e5;
}
.logo:hover { opacity: 0.8; }

.right { display: flex; gap: 10px; align-items: center; }
.user { color: #4b5563; font-size: 14px; font-weight: 500; }
.header-btn {
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}
.header-btn:hover {
  background-color: #f9fafb;
  border-color: #9ca3af;
}
.main {
  max-width: 1024px;
  margin: 32px auto;
  padding: 0 24px;
}

*,
*::before,
*::after {
  box-sizing: border-box;
}
</style>
