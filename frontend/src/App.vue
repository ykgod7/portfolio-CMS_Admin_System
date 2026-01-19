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
.wrap { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
.top {
  max-width: 920px;
  margin: 20px auto 0;
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  border: 1px solid #ddd;
  border-radius: 12px;
}

/* 로고를 strong 대신 버튼처럼(접근성+클릭) */
.logo {
  font-weight: 700;
  font-size: 16px;
  border: none;
  background: transparent;
  cursor: pointer;
  padding: 0;
}
.logo:hover { text-decoration: underline; }

.right { display: flex; gap: 10px; align-items: center; }
.user { color: #555; font-size: 14px; }
.header-btn {
  padding: 8px 10px;
  border: 1px solid #333;
  border-radius: 10px;
  background: white;
  cursor: pointer;
}
.main {
  max-width: 920px;
  margin: 16px auto;
}

*,
*::before,
*::after {
  box-sizing: border-box;
}
</style>
