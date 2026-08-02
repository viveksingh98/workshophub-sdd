<section class="admin-panel">
  <h2>Search results @if ($query !== '') for “{{ $query }}” @endif</h2>
  @if ($query === '')
    <p class="field-note">Type in the top-bar search box to find students, bookings, posts, records, and FAQs.</p>
  @else
    @php($total = collect($results)->sum(fn ($group) => $group->count()))
    @if ($total === 0)
      <p class="field-note">Nothing matched “{{ $query }}” — try a shorter fragment.</p>
    @endif
    @foreach ($results as $group => $items)
      @if ($items->isNotEmpty())
        <h3>{{ $group }}</h3>
        <ul class="meta-list spaced">
          @foreach ($items as $item)
            <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
          @endforeach
        </ul>
      @endif
    @endforeach
  @endif
</section>
