<section class="admin-panel">
  <h2>{{ $editPost ? 'Edit post' : 'Write a post' }}</h2>
  <form class="form-grid" method="post" enctype="multipart/form-data"
        action="{{ $editPost ? route('dashboard.posts.update', $editPost) : route('dashboard.posts.store') }}">
    @csrf
    <label>Title <input name="title" value="{{ old('title', $editPost->title ?? '') }}" required></label>
    <label>Excerpt <textarea name="excerpt" required>{{ old('excerpt', $editPost->excerpt ?? '') }}</textarea></label>
    <label>Content
      <div class="wysiwyg" data-wysiwyg>
        <div class="wysiwyg-toolbar">
          <button type="button" data-cmd="bold"><b>B</b></button>
          <button type="button" data-cmd="italic"><i>I</i></button>
          <button type="button" data-cmd="insertUnorderedList">• list</button>
          <button type="button" data-cmd="formatBlock" data-value="h2">H2</button>
          <button type="button" data-cmd="formatBlock" data-value="blockquote">❝</button>
        </div>
        <div class="wysiwyg-area" contenteditable="true" data-wysiwyg-area>{!! old('content', $editPost->content ?? '') !!}</div>
        <textarea name="content" class="is-hidden" data-wysiwyg-input>{{ old('content', $editPost->content ?? '') }}</textarea>
      </div>
    </label>
    <div class="form-row">
      <label>Category
        <select name="category">
          @foreach ($categories as $category)
            <option @selected(($editPost->category ?? '') === $category)>{{ $category }}</option>
          @endforeach
        </select>
      </label>
      <label>Publish date <input name="published_at" type="date" value="{{ old('published_at', ($editPost->published_at ?? now())->format('Y-m-d')) }}"></label>
    </div>
    <div class="form-row">
      <label>Status <select name="status"><option @selected(($editPost->status ?? '') === 'Draft')>Draft</option><option @selected(($editPost->status ?? '') === 'Published')>Published</option></select></label>
      <label>Cover image <input name="image" type="file" accept="image/*"></label>
    </div>
    <button class="button primary" type="submit">{{ $editPost ? 'Update post' : 'Publish once, live everywhere' }}</button>
  </form>
</section>

<section class="admin-panel">
  <h2>All posts</h2>
  <div class="post-list">
    @forelse ($posts as $post)
      <article class="post-card">
        <div class="section-head">
          <h3>{{ $post->title }}</h3>
          <div class="button-row">
            <a class="button" href="{{ route('dashboard', ['section' => 'blog', 'edit' => $post->id]) }}">Edit</a>
            <form method="post" action="{{ route('dashboard.posts.delete', $post) }}">@csrf<button class="button danger" type="submit">Delete</button></form>
          </div>
        </div>
        <span class="tag">{{ $post->status }}</span> <span class="tag">{{ $post->category }}</span>
        @if ($post->image_path) <span class="tag">🖼 image</span> @endif
        <p>{{ $post->excerpt }}</p>
      </article>
    @empty
      <p class="field-note">No posts yet — the editor above publishes straight to the public site.</p>
    @endforelse
  </div>
</section>
