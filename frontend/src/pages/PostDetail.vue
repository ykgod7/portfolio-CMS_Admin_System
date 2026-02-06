<template>
  <div class="page">
    <header class="page-header">
      <div class="page-header__text">
        <h1 class="title">게시글 상세</h1>
        <p class="desc">게시글 내용을 확인합니다.</p>
      </div>

      <div class="actions">
        <button class="btn ghost" type="button" @click="goList">목록으로</button>

        <!-- ✅ 작성자 본인일 때만 노출 -->
        <button
          v-if="canDelete"
          class="btn danger"
          type="button"
          @click="onDelete"
          :disabled="loadingDelete || loading"
        >
          {{ loadingDelete ? "삭제 중..." : "삭제" }}
        </button>
      </div>
    </header>

    <main class="content">
      <div v-if="loading" class="state">불러오는 중...</div>

      <div v-else-if="error" class="state error">
        <p class="state__msg">{{ error }}</p>
        <button class="btn ghost" type="button" @click="fetchPost">다시 시도</button>
      </div>

      <div v-else-if="!post" class="state">
        게시글이 존재하지 않습니다.
        <button class="btn ghost" type="button" @click="goList">목록으로</button>
      </div>

      <article v-else class="card">
        <div class="card-head">
          <h2 class="post-title">{{ post.title }}</h2>

          <div class="meta">
            <span class="meta__item">#{{ post.id }}</span>
            <span class="meta__dot">·</span>
            <span class="meta__item">{{ formatDate(post.created_at) }}</span>
            <span v-if="post.author_name" class="meta__dot">·</span>
            <span v-if="post.author_name" class="meta__item">{{ post.author_name }}</span>
          </div>
        </div>

        <div class="divider"></div>

        <div class="post-body">
          <p class="post-content">{{ post.content }}</p>
        </div>
      </article>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from "../lib/api";
import { useAuthStore } from "../stores/auth";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const post = ref(null);
const loading = ref(false);
const error = ref("");

const loadingDelete = ref(false);

const postId = computed(() => Number(route.params.id));

// ✅ 작성자 본인인지
const isOwner = computed(() => {
  const meId = auth.user?.id;
  const authorId = post.value?.author_id;
  if (!meId || !authorId) return false;
  return Number(meId) === Number(authorId);
});

// ✅ 삭제 버튼 노출 조건(일단 “본인”만)
const canDelete = computed(() => isOwner.value);

const formatDate = (v) => {
  if (!v) return "";
  const d = new Date(v);
  return isNaN(d.getTime()) ? String(v) : d.toLocaleString("ko-KR");
};

const goList = () => router.push("/posts");

const fetchPost = async () => {
  loading.value = true;
  error.value = "";

  try {
    const res = await api.get("/posts.php", { params: { id: postId.value } });
    post.value = res.data.data; // 상세는 객체
  } catch (e) {
    const status = e?.response?.status;
    if (status === 404) error.value = "게시글이 존재하지 않습니다.";
    else if (status === 401) error.value = "로그인이 필요합니다.";
    else error.value = "게시글을 불러오지 못했습니다.";
  } finally {
    loading.value = false;
  }
};

const onDelete = async () => {
  if (!post.value) return;
  if (!confirm("정말 삭제할까요?")) return;

  loadingDelete.value = true;
  error.value = "";

  try {
    // ✅ 삭제 API (예: DELETE /posts.php?id=123)
    await api.delete("/posts.php", { params: { id: postId.value } });

    router.replace("/posts");
  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      e?.message ||
      "삭제에 실패했습니다.";
  } finally {
    loadingDelete.value = false;
  }
};

onMounted(fetchPost);
</script>

<style lang="scss" scoped>
  /* PostDetail.vue (scoped) */

.page {
  padding-bottom: 40px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 16px;
}

.page-header__text {
  min-width: 0;
}

.title {
  margin: 0;
  font-size: 28px;
  font-weight: 800;
}

.desc {
  margin: 6px 0 0;
  color: #6b7280;
}

.actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Content / States */
.content {
  /* Wrapper for states, card handles its own style */
}

.state {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 18px;
  color: #555;
}

.state.error {
  color: #ef4444;
}

.state__msg {
  margin: 0;
}

/* Card */
.card {
  padding: 40px;
  background: #fff;
  border: none;
  border-radius: 16px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  max-width: unset;
}

.card-head {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.post-title {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  line-height: 1.35;
  word-break: break-word;
}

.meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #6b7280;
}

.meta__item {
  white-space: nowrap;
}

.meta__dot {
  color: #d1d5db;
}

.divider {
  height: 1px;
  margin: 24px 0;
  background: #e5e7eb;
}

.post-content {
  margin: 0;
  font-size: 16px;
  line-height: 1.8;
  color: #374151;
  white-space: pre-wrap; /* 줄바꿈 유지 */
  word-break: break-word;
  min-height: 200px;
}

/* Buttons */
.btn {
  height: 36px;
  padding: 0 16px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: #4f46e5;
  color: #fff;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.btn.ghost {
  background: #fff;
  color: #374151;
  border-color: #d1d5db;
}

.btn.danger {
  background: #ef4444;
  border-color: #ef4444;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn:hover:not(:disabled) {
  background: #4338ca;
  border-color: #4338ca;
}

.btn.ghost:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn.danger:hover:not(:disabled) {
  background: #dc2626;
  border-color: #dc2626;
}
</style>
