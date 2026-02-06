<template>
  <div class="page">
    <header class="page-header">
      <div class="page-header__text">
        <h1 class="title">새 게시글 작성</h1>
        <p class="desc">제목과 내용을 입력해 게시글을 생성합니다.</p>
      </div>

      <div class="actions">
        <button class="btn ghost" type="button" @click="goList">목록으로</button>
      </div>
    </header>

    <main class="content">
      <form class="form" @submit.prevent="onSubmit">
        <div class="field">
          <label class="label" for="title">제목</label>
          <input
            id="title"
            v-model.trim="form.title"
            class="input"
            type="text"
            placeholder="제목을 입력하세요"
            maxlength="120"
            autocomplete="off"
          />
          <p class="hint">
            {{ form.title.length }}/120
          </p>
        </div>

        <div class="field">
          <label class="label" for="content">내용</label>
          <textarea
            id="content"
            v-model.trim="form.content"
            class="textarea"
            rows="12"
            placeholder="내용을 입력하세요"
          />
          <p class="hint">
            {{ form.content.length }}자
          </p>
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <div class="footer">
          <button class="btn ghost" type="button" @click="onReset" :disabled="loading">
            초기화
          </button>
          <button class="btn" type="submit" :disabled="!canSubmit || loading">
            {{ loading ? "등록 중..." : "등록" }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";
import { useRouter } from "vue-router";
import { api } from "../lib/api";

const router = useRouter();

const loading = ref(false);
const error = ref("");

const form = reactive({
  title: "",
  content: "",
});

const canSubmit = computed(() => {
  const t = form.title.trim();
  const c = form.content.trim();
  return t.length >= 2 && c.length >= 5;
});

function goList() {
  router.push("/posts");
}

function onReset() {
  error.value = "";
  form.title = "";
  form.content = "";
}

async function onSubmit() {
  error.value = "";

  if (!canSubmit.value) {
    error.value = "제목(2자 이상)과 내용(5자 이상)을 입력해 주세요.";
    return;
  }

  loading.value = true;

  try {
    await api.post("/posts.php", {
      title: form.title.trim(),
      content: form.content.trim(),
    });

    // 생성 후 목록으로 이동
    router.push("/posts");
  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      e?.message ||
      "게시글 생성에 실패했습니다.";
  } finally {
    loading.value = false;
  }
}
</script>

<style lang="scss" scoped>
.page {
  padding-bottom: 40px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-end;
  margin-bottom: 16px;

  &__text {
    min-width: 0;
  }
}

.title {
  font-size: 28px;
  margin: 0;
  font-weight: 800;
}

.desc {
  margin: 6px 0 0;
  color: #6b7280;
}

.actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.content {
  border: none;
  border-radius: 16px;
  padding: 32px;
  background: #fff;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.label {
  font-size: 14px;
  color: #374151;
  font-weight: 700;
}

.input {
  height: 42px;
  padding: 0 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  transition: all 0.2s;

  &:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }
}

.textarea {
  padding: 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  resize: vertical;
  transition: all 0.2s;

  &:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }
}

.hint {
  margin: 0;
  font-size: 12px;
  color: #888;
}

.error {
  margin: 2px 0 0;
  color: #ef4444;
  font-size: 13px;
}

.footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 6px;
}

.btn {
  height: 36px;
  padding: 0 16px;
  border-radius: 8px;
  border: 1px solid transparent;
  background: #4f46e5;
  color: #fff;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;

  &:hover:not(:disabled) {
    background: #4338ca;
  }

  &.ghost {
    background: #fff;
    color: #374151;
    border-color: #d1d5db;
  }
  &.ghost:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #9ca3af;
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}
</style>
