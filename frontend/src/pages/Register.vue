<template>
  <div class="card">
    <h2>회원가입</h2>
    <p class="sub">이메일과 비밀번호를 입력해 계정을 생성합니다.</p>

    <label class="label">Name</label>
    <input class="input" v-model.trim="name" placeholder="이름" />

    <label class="label" style="margin-top: 12px;">Email</label>
    <input class="input" v-model.trim="email" placeholder="example@email.com" />

    <label class="label" style="margin-top: 12px;">Password</label>
    <input class="input" type="password" v-model="password" placeholder="비밀번호(최소 4자)" />

    <label class="label" style="margin-top: 12px;">Password 확인</label>
    <input class="input" type="password" v-model="password2" placeholder="비밀번호 확인" />

    <p v-if="localError" class="error">{{ localError }}</p>
    <p v-else-if="auth.error" class="error">{{ auth.error }}</p>

    <button class="login-btn" :disabled="auth.loading || !canSubmit" @click="onRegister">
      {{ auth.loading ? "가입 중..." : "회원가입" }}
    </button>

    <div class="hint">
      <span>이미 계정이 있나요?</span>
      <router-link to="/login">로그인</router-link>
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

.login-btn:hover:not(:disabled) {
  background-color: #111;
  color: #fff;
  border-color: #111;
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: crimson;
  margin-top: 10px;
}

.hint {
  margin-top: 12px;
  font-size: 14px;
  color: #444;
  display: flex;
  gap: 8px;
  align-items: center;
}
</style>
