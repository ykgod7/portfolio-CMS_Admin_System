<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <h1 class="auth-title">로그인</h1>
        <p class="auth-sub">관리자 대시보드에 접속합니다.</p>
        <p class="test-account">테스트 계정: admin@example.com / 1234</p>
      </div>

      <form @submit.prevent="onLogin" class="auth-form">
        <div class="field">
          <label class="label">이메일</label>
          <input
            class="input"
            v-model="email"
            placeholder="example@email.com"
            type="email"
            required
          />
        </div>

        <div class="field">
          <label class="label">비밀번호</label>
          <input
            class="input"
            type="password"
            v-model="password"
            placeholder="비밀번호 입력"
            required
          />
        </div>

        <div v-if="auth.error" class="error-msg">{{ auth.error }}</div>

        <button class="btn-submit" type="submit" :disabled="auth.loading">
          {{ auth.loading ? "로그인 중..." : "로그인" }}
        </button>
      </form>

      <div class="auth-footer">
        <span>계정이 없으신가요?</span>
        <router-link to="/register" class="link">회원가입</router-link>
      </div>
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
  if (auth.user) router.replace("/posts");
});

async function onLogin() {
  try {
    await auth.login(email.value, password.value);
    router.replace("/posts");
  } catch {
    // auth.error로 이미 표시
  }
}
</script>

<style lang="scss" scoped>
.auth-page {
  display: flex;
  justify-content: center;
  padding-top: 40px;
  padding-bottom: 40px;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.auth-header {
  text-align: center;
  margin-bottom: 32px;
}

.auth-title {
  font-size: 24px;
  font-weight: 800;
  color: #111827;
  margin: 0;
}

.auth-sub {
  margin: 8px 0 0;
  font-size: 14px;
  color: #6b7280;
}

.test-account {
  margin: 4px 0 0;
  font-size: 13px;
  color: #9ca3af;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.input {
  height: 42px;
  padding: 0 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  transition: all 0.2s;
  font-size: 14px;

  &:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }
}

.error-msg {
  font-size: 13px;
  color: #ef4444;
  text-align: center;
}

.btn-submit {
  height: 44px;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-top: 8px;

  &:hover:not(:disabled) {
    background: #4338ca;
  }
  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
}

.auth-footer {
  margin-top: 24px;
  text-align: center;
  font-size: 14px;
  color: #6b7280;
  display: flex;
  justify-content: center;
  gap: 6px;
}

.link {
  color: #4f46e5;
  text-decoration: none;
  font-weight: 600;
  
  &:hover {
    text-decoration: underline;
  }
}
</style>
