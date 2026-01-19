<template>
  <div class="card">
    <h2>로그인</h2>
    <p class="sub">테스트 계정: admin@example.com / 1234</p>

    <label class="label">Email</label>
    <input class="input" v-model="email" />

    <label class="label" style="margin-top: 12px;">Password</label>
    <input class="input" type="password" v-model="password" />

    <div class="btn-container">
      <button class="login-btn" :disabled="auth.loading" @click="onLogin">
        {{ auth.loading ? "로그인 중..." : "로그인" }}
      </button>
      <button class="register-btn" @click="onRegister">
        회원가입
      </button>
    </div>

    <div class="hint" v-if="auth.user">
      <p>로그인됨: {{ auth.user.email }}</p>
      <router-link to="/admin">/admin 가기</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, watchEffect } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const router = useRouter();

const email = ref("admin@example.com");
const password = ref("1234");

watchEffect(() => {
  if (auth.user?.role === "admin") router.replace("/admin");
});

function onRegister() {
  router.push('/register');
}

async function onLogin() {
  try {
    await auth.login(email.value, password.value);
    router.replace("/posts");
  } catch {
    // auth.error로 이미 표시
  }
}
</script>

<style>
.card {
  max-width: 420px;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px;
}

.sub { 
  color: #666; 
  margin-top: -8px; 
}

.label { 
  display: block; 
  margin-top: 10px; 
  margin-bottom: 6px; 
  font-size: 14px; 
}

.input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #bbb;
  border-radius: 10px;
}

.btn-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.register-btn {
  width: 50%;
  padding: 10px 12px;
  border: 1px solid #333;
  border-radius: 10px;
  background: white;
  margin-top: 20px;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.register-btn:hover {
  background-color: #111;
  color: #fff;
  border-color: #111;
}

.login-btn {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #333;
  border-radius: 10px;
  background: white;
  margin-top: 20px;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.login-btn:hover {
  background-color: #111;
  color: #fff;
  border-color: #111;
}

.error { 
  color: crimson; 
  margin-top: 10px; 
}

.hint { 
  margin-top: 12px; 
  font-size: 14px; 
  color: #444; 
  }
</style>
