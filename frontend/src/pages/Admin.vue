```vue
<template>
  <div class="admin">
    <header class="admin__header">
      <div>
        <h1 class="admin__title">관리자 페이지</h1>
        <p class="admin__desc">게시글/유저 관리</p>
      </div>

      <div class="admin__actions">
        <button class="btn ghost" type="button" @click="refreshAll" :disabled="busy">
          새로고침
        </button>
      </div>
    </header>

    <div class="stack">
      <!-- POSTS -->
      <section class="panel">
        <header class="panel__header">
          <div class="panel__title-wrap">
            <h2 class="panel__title">전체 게시글</h2>
            <p class="panel__sub">조회/삭제</p>
          </div>

          <div class="panel__controls">
            <input
              v-model.trim="postsQ"
              class="input"
              type="search"
              placeholder="제목/작성자 검색"
              @keyup.enter="refetchPosts"
            />
            <label class="toggle">
              <input type="checkbox" v-model="includeDeleted" @change="refetchPosts" />
              <span>삭제 포함</span>
            </label>
          </div>
        </header>

        <div class="panel__body">
          <div v-if="postsLoading" class="state">불러오는 중...</div>
          <div v-else-if="postsError" class="state error">{{ postsError }}</div>

          <ul v-else class="list">
            <li v-for="p in posts" :key="p.id" class="list__item">
              <div class="list__main">
                <div class="list__title-row">
                  <span class="list__title">{{ p.title }}</span>
                  <span v-if="p.deleted_at" class="badge">삭제됨</span>
                </div>

                <div class="meta">
                  <span>#{{ p.id }}</span>
                  <span class="dot">·</span>
                  <span>{{ formatDate(p.created_at) }}</span>
                  <template v-if="p.author_name">
                    <span class="dot">·</span>
                    <span>{{ p.author_name }}</span>
                  </template>
                </div>
              </div>

              <div class="list__actions">
                <button class="btn ghost" type="button" @click="goPost(p.id)">
                  보기
                </button>
                <button
                  class="btn danger"
                  type="button"
                  :disabled="busy || !!p.deleted_at"
                  @click="deletePost(p.id)"
                  title="소프트 삭제"
                >
                  삭제
                </button>
              </div>
            </li>
          </ul>

          <footer class="pager">
            <button class="btn ghost" :disabled="postsPage <= 1 || postsLoading" @click="postsPage--">
              이전
            </button>
            <span class="pager__text">Page {{ postsPage }}</span>
            <button class="btn ghost" :disabled="postsLoading || posts.length < postsSize" @click="postsPage++">
              다음
            </button>
          </footer>
        </div>
      </section>

      <!-- USERS -->
      <section class="panel">
        <header class="panel__header">
          <div class="panel__title-wrap">
            <h2 class="panel__title">유저 목록</h2>
            <p class="panel__sub">role 변경</p>
          </div>

          <div class="panel__controls">
            <input
              v-model.trim="usersQ"
              class="input"
              type="search"
              placeholder="이름/이메일 검색"
              @keyup.enter="refetchUsers"
            />
          </div>
        </header>

        <div class="panel__body">
          <div v-if="usersLoading" class="state">불러오는 중...</div>
          <div v-else-if="usersError" class="state error">{{ usersError }}</div>

          <div v-else class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 56px;">ID</th>
                  <th>이름</th>
                  <th>이메일</th>
                  <th style="width: 140px;">Role</th>
                  <th style="width: 92px;">저장</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in filteredUsers" :key="u.id">
                  <td class="mono">{{ u.id }}</td>
                  <td>{{ u.name }}</td>
                  <td class="mono">{{ u.email }}</td>
                  <td>
                    <select class="select" v-model="roleDraft[u.id]" :disabled="busy">
                      <option value="user">user</option>
                      <option value="admin">admin</option>
                    </select>
                  </td>
                  <td>
                    <button
                      class="btn small"
                      type="button"
                      :disabled="busy || roleDraft[u.id] === u.role"
                      @click="saveRole(u)"
                    >
                      저장
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <p class="hint">
              role 변경은 즉시 반영되며, 변경된 유저는 재로그인이 필요할 수 있습니다.
            </p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { api } from "../lib/api";

const router = useRouter();
const busy = ref(false);

/** --------------------
 *  POSTS
 * -------------------*/
const posts = ref([]);
const postsLoading = ref(false);
const postsError = ref("");
const postsQ = ref("");
const includeDeleted = ref(false);

const postsPage = ref(1);
const postsSize = 20;

const formatDate = (v) => {
  if (!v) return "";
  const d = new Date(v);
  return isNaN(d.getTime()) ? String(v) : d.toLocaleString("ko-KR");
};

const goPost = (id) => router.push({ name: "Post", params: { id } });

const fetchPosts = async () => {
  postsLoading.value = true;
  postsError.value = "";

  try {
    const res = await api.get("/posts.php", {
      params: {
        q: postsQ.value || undefined,
        page: postsPage.value,
        size: postsSize,
        include_deleted: includeDeleted.value ? 1 : 0,
      },
    });

    posts.value = res.data?.data?.items ?? res.data?.items ?? res.data?.data ?? [];
  } catch (e) {
    postsError.value = e?.response?.data?.message || e?.message || "게시글을 불러오지 못했습니다.";
  } finally {
    postsLoading.value = false;
  }
};

const refetchPosts = () => {
  postsPage.value = 1;
  fetchPosts();
};

const deletePost = async (id) => {
  if (!confirm(`게시글 #${id} 을(를) 삭제할까요?`)) return;

  busy.value = true;
  try {
    await api.delete("/posts.php", { params: { id } });
    await fetchPosts();
  } catch (e) {
    alert(e?.response?.data?.message || "삭제 실패");
  } finally {
    busy.value = false;
  }
};

watch(postsPage, fetchPosts);

/** --------------------
 *  USERS
 * -------------------*/
const users = ref([]);
const usersLoading = ref(false);
const usersError = ref("");
const usersQ = ref("");
const roleDraft = reactive({}); // { [userId]: 'user' | 'admin' }

const fetchUsers = async () => {
  usersLoading.value = true;
  usersError.value = "";

  try {
    const res = await api.get("/admin/users.php");
    users.value = res.data?.data?.items ?? res.data?.items ?? res.data?.data ?? [];

    for (const u of users.value) roleDraft[u.id] = u.role;
  } catch (e) {
    usersError.value = e?.response?.data?.message || e?.message || "유저 목록을 불러오지 못했습니다.";
  } finally {
    usersLoading.value = false;
  }
};

const filteredUsers = computed(() => {
  const q = usersQ.value.trim().toLowerCase();
  if (!q) return users.value;

  return users.value.filter((u) => {
    const name = String(u.name ?? "").toLowerCase();
    const email = String(u.email ?? "").toLowerCase();
    return name.includes(q) || email.includes(q);
  });
});

const refetchUsers = () => fetchUsers();

const saveRole = async (u) => {
  const nextRole = roleDraft[u.id];
  if (nextRole === u.role) return;

  if (!confirm(`${u.email} 의 role을 ${u.role} → ${nextRole} 로 변경할까요?`)) {
    roleDraft[u.id] = u.role;
    return;
  }

  busy.value = true;
  try {
    await api.patch("/admin/users.php", { id: u.id, role: nextRole });
    u.role = nextRole;
  } catch (e) {
    roleDraft[u.id] = u.role;
    alert(e?.response?.data?.message || "role 변경 실패");
  } finally {
    busy.value = false;
  }
};

/** --------------------
 *  INIT
 * -------------------*/
const refreshAll = async () => {
  await Promise.all([fetchPosts(), fetchUsers()]);
};

onMounted(refreshAll);
</script>

<style scoped lang="scss">
.admin {
  padding-bottom: 40px;
}

.admin__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 16px;
}

.admin__title {
  margin: 0;
  font-size: 28px;
  font-weight: 800;
}

.admin__desc {
  margin: 6px 0 0;
  color: #6b7280;
}

.admin__actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

/* ✅ 위/아래 스택 */
.stack {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* Panels */
.panel {
  background: #fff;
  border: none;
  border-radius: 16px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.panel__header {
  padding: 20px 24px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-end;
}

.panel__title-wrap {
  min-width: 0;
}

.panel__title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
}

.panel__sub {
  margin: 6px 0 0;
  font-size: 13px;
  color: #6b7280;
}

.panel__controls {
  display: flex;
  gap: 10px;
  align-items: center;
}

.panel__body {
  padding: 0; /* Remove padding for flush list/table */
}

/* Inputs */
.input {
  width: 260px;
  height: 36px;
  padding: 0 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  transition: border-color 0.2s;
}

.input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.toggle {
  display: inline-flex;
  gap: 6px;
  align-items: center;
  font-size: 13px;
  color: #374151;
  user-select: none;
}

/* States */
.state {
  padding: 18px;
  color: #555;
}

.state.error {
  color: #ef4444;
}

/* Posts list */
.list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.list__item {
  padding: 16px 24px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  transition: background-color 0.1s;
}

.list__item:hover {
  background-color: #f9fafb;
}

.list__main {
  min-width: 0;
}

.list__title-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.list__title {
  font-weight: 700;
  color: #1f2937;
  word-break: break-word;
}

.badge {
  font-size: 12px;
  background: #f3f4f6;
  padding: 2px 8px;
  border-radius: 999px;
  color: #6b7280;
}

.meta {
  margin-top: 6px;
  font-size: 13px;
  color: #9ca3af;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  align-items: center;
}

.dot {
  color: #d1d5db;
}

.list__actions {
  display: flex;
  gap: 8px;
  align-items: flex-end;
}

/* Pager */
.pager {
  display: flex;
  justify-content: center;
  gap: 10px;
  padding: 16px;
}

.pager__text {
  font-size: 13px;
  color: #666;
  line-height: 36px;
}

/* Users table */
.table-wrap {
  overflow: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  min-width: 760px;
}

.table th,
.table td {
  padding: 14px 24px;
  border-bottom: 1px solid #f3f4f6;
  text-align: left;
  font-size: 13px;
  vertical-align: middle;
}

.table th {
  background: #f9fafb;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-size: 11px;
}

.mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New",
    monospace;
}

.select {
  height: 34px;
  padding: 0 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  background: #fff;
}

.select:focus {
  border-color: #4f46e5;
}

.hint {
  margin: 16px 24px;
  font-size: 12px;
  color: #9ca3af;
}

/* Buttons */
.btn {
  height: 36px;
  padding: 0 16px;
  border-radius: 8px;
  border: 1px solid transparent;
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

.btn.small {
  height: 30px;
  padding: 0 12px;
  font-size: 12px;
  border-radius: 6px;
  width: max-content;
}

.btn:disabled {
  opacity: 0.55;
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
```
