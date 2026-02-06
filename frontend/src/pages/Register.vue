<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <h1 class="auth-title">회원가입</h1>
        <p class="auth-sub">서비스 사용을 위해 계정을 생성하세요.</p>
      </div>

      <form @submit.prevent="onRegister" class="auth-form">
        <div class="field">
          <label class="label">이름</label>
          <input
            class="input"
            v-model.trim="name"
            placeholder="홍길동"
            type="text"
          />
        </div>

        <div class="field">
          <label class="label">이메일</label>
          <input
            class="input"
            v-model.trim="email"
            placeholder="example@email.com"
            type="email"
          />
        </div>

        <div class="field">
          <label class="label">비밀번호</label>
          <input
            class="input"
            type="password"
            v-model="password"
            placeholder="4자 이상 입력"
          />
        </div>

        <div class="field">
          <label class="label">비밀번호 확인</label>
          <input
            class="input"
            type="password"
            v-model="password2"
            placeholder="비밀번호를 다시 입력하세요"
          />
        </div>

        <div v-if="localError" class="error-msg">{{ localError }}</div>
        <div v-else-if="auth.error" class="error-msg">{{ auth.error }}</div>

        <button class="btn-submit" type="submit" :disabled="auth.loading || !canSubmit">
          {{ auth.loading ? "가입 중..." : "회원가입" }}
        </button>
      </form>

      <div class="auth-footer">
        <span>이미 계정이 있으신가요?</span>
        <router-link to="/login" class="link">로그인</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watchEffect } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const router = useRouter();

const name = ref("");
const email = ref("");
const password = ref("");
const password2 = ref("");
const localError = ref("");

const canSubmit = computed(() => {
  return (
    name.value.trim().length >= 2 &&
    email.value.trim().length >= 5 &&
    password.value.length >= 4 &&
    password2.value.length >= 4
  );
});

watchEffect(() => {
  // 이미 로그인 상태면 posts로
  if (auth.user) router.replace("/posts");
});

async function onRegister() {
  localError.value = "";

  if (password.value !== password2.value) {
    localError.value = "비밀번호가 일치하지 않습니다.";
    return;
  }

  try {
    await auth.register({
      name: name.value.trim(),
      email: email.value.trim(),
      password: password.value,
    });

    // 가입 후 바로 로그인 처리(백엔드가 세션 세팅해주면)
    router.replace("/posts");
  } catch (e) {
    console.log(e)
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
