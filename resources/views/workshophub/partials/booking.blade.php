<div class="section-head">
  <div>
    <h2>Booking flow</h2>
    <p>Required details, capacity check, booking request, student profile update, and confirmation.</p>
  </div>
</div>

<div class="two-column">
  <section class="panel"><h2>Visitor details</h2>@include('workshophub.partials.booking-form')</section>
  <section class="panel"><h2>Recent booking requests</h2>@include('workshophub.partials.booking-table', ['compact' => true])</section>
</div>
