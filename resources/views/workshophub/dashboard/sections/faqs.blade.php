<section class="admin-panel">
  <h2>Frequently asked questions</h2>
  <form class="form-grid" method="post" action="{{ route('dashboard.faqs.store') }}">
    @csrf
    <label>Question <input name="question" required></label>
    <label>Answer <textarea name="answer" required></textarea></label>
    <button class="button primary" type="submit">Add FAQ</button>
  </form>

  <div class="faq-list spaced">
    @forelse ($faqs as $faq)
      <article class="faq-item">
        <div class="section-head">
          <h3>{{ $faq->question }}</h3>
          <form method="post" action="{{ route('dashboard.faqs.delete', $faq) }}">@csrf<button class="button danger" type="submit">Remove</button></form>
        </div>
        <p>{{ $faq->answer }}</p>
      </article>
    @empty
      <p class="field-note">No FAQs yet — they render as an accordion on the public homepage.</p>
    @endforelse
  </div>
</section>
