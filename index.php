<?php include 'container/header.php'; ?>
<section class="bg-jet-cream">
      <div class="grid overflow-hidden lg:grid-cols-[1.03fr_0.97fr]">
        <div class="bg-jet-orange px-5 py-7 sm:px-8 sm:py-10 md:px-10 md:py-12 lg:px-14 lg:py-16 xl:px-16 xl:py-20">
          <div class="mx-auto max-w-[660px]">
            <h1 class="hero-mission-title max-w-[12ch] text-[3.1rem] leading-[0.94] tracking-[-0.06em] sm:text-[3.5rem] md:text-[3.4rem] lg:text-[3.4rem] xl:text-[3.25rem]">
              SURROUND YOURSELF WITH THOSE ON THE SAME MISSION AS YOU!
            </h1>

            <form id="job-search-form" action="search-results.php" method="get" class="mt-8 flex flex-col gap-3 sm:flex-row" role="search" aria-label="Search jobs">
              <label for="job-search" class="sr-only">Search for job title</label>
              <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-6 top-1/2 h-7 w-7 -translate-y-1/2 text-jet-charcoal" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M11 5a6 6 0 1 0 0 12a6 6 0 0 0 0-12Zm8 14l-3.25-3.25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <input id="job-search" name="keywords" type="search" placeholder="Search for job title" class="h-16 w-full rounded-full border-0 bg-white px-16 text-lg font-semibold text-jet-charcoal shadow-soft outline-none ring-0 placeholder:text-jet-charcoal/75 focus:outline-none focus:ring-2 focus:ring-white/70" autocomplete="off">
              </div>
              <button type="submit" class="h-16 rounded-full bg-jet-charcoal px-9 text-xl font-bold text-white transition hover:bg-black sm:min-w-[165px] lg:min-w-[190px]">
                Search
              </button>
            </form>
          </div>
        </div>

        <div class="min-h-[260px] sm:min-h-[320px] lg:min-h-0">
          <img src="images/banner.webp" alt="People sharing food together" class="h-full w-full object-cover object-center">
        </div>
      </div>
    </section>

    <section id="countries" class="overflow-hidden bg-jet-cream py-8 sm:py-10">
      <h2 id="countries-heading" class="sr-only">Canadian cities and business categories</h2>

      <div class="countries-marquee-stack" aria-labelledby="countries-heading">
        <div class="countries-marquee" aria-label="Canadian cities">
          <div class="countries-marquee-track countries-marquee-track-right" data-marquee-track>
            <ul class="countries-marquee-group" role="list">
              <li class="countries-marquee-item countries-marquee-item-city">Toronto</li>
              <li class="countries-marquee-item countries-marquee-item-city">Montreal</li>
              <li class="countries-marquee-item countries-marquee-item-city">Calgary</li>
              <li class="countries-marquee-item countries-marquee-item-city">Edmonton</li>
              <li class="countries-marquee-item countries-marquee-item-city">Ottawa</li>
              <li class="countries-marquee-item countries-marquee-item-city">Winnipeg</li>
              <li class="countries-marquee-item countries-marquee-item-city">Brampton</li>
              <li class="countries-marquee-item countries-marquee-item-city">Mississauga</li>
              <li class="countries-marquee-item countries-marquee-item-city">Vancouver</li>
              <li class="countries-marquee-item countries-marquee-item-city">Surrey</li>
              <li class="countries-marquee-item countries-marquee-item-city">Hamilton</li>
              <li class="countries-marquee-item countries-marquee-item-city">Quebec City</li>
              <li class="countries-marquee-item countries-marquee-item-city">Halifax</li>
              <li class="countries-marquee-item countries-marquee-item-city">London</li>
              <li class="countries-marquee-item countries-marquee-item-city">Laval</li>
              <li class="countries-marquee-item countries-marquee-item-city">Markham</li>
              <li class="countries-marquee-item countries-marquee-item-city">Vaughan</li>
              <li class="countries-marquee-item countries-marquee-item-city">Kitchener</li>
              <li class="countries-marquee-item countries-marquee-item-city">Saskatoon</li>
              <li class="countries-marquee-item countries-marquee-item-city">Gatineau</li>
              <li class="countries-marquee-item countries-marquee-item-city">Burnaby</li>
              <li class="countries-marquee-item countries-marquee-item-city">Longueuil</li>
              <li class="countries-marquee-item countries-marquee-item-city">Windsor</li>
              <li class="countries-marquee-item countries-marquee-item-city">Regina</li>
              <li class="countries-marquee-item countries-marquee-item-city">Richmond</li>
              <li class="countries-marquee-item countries-marquee-item-city">Oakville</li>
              <li class="countries-marquee-item countries-marquee-item-city">Richmond Hill</li>
              <li class="countries-marquee-item countries-marquee-item-city">Oshawa</li>
              <li class="countries-marquee-item countries-marquee-item-city">Burlington</li>
              <li class="countries-marquee-item countries-marquee-item-city">Sudbury</li>
              <li class="countries-marquee-item countries-marquee-item-city">Kingston</li>
              <li class="countries-marquee-item countries-marquee-item-city">Abbotsford</li>
              <li class="countries-marquee-item countries-marquee-item-city">St. Catharines</li>
              <li class="countries-marquee-item countries-marquee-item-city">Cambridge</li>
              <li class="countries-marquee-item countries-marquee-item-city">Kelowna</li>
              <li class="countries-marquee-item countries-marquee-item-city">Barrie</li>
              <li class="countries-marquee-item countries-marquee-item-city">Victoria</li>
              <li class="countries-marquee-item countries-marquee-item-city">Thunder Bay</li>
              <li class="countries-marquee-item countries-marquee-item-city">Nanaimo</li>
              <li class="countries-marquee-item countries-marquee-item-city">Brantford</li>
              <li class="countries-marquee-item countries-marquee-item-city">Moncton</li>
              <li class="countries-marquee-item countries-marquee-item-city">Lethbridge</li>
              <li class="countries-marquee-item countries-marquee-item-city">Trois-Rivières</li>
              <li class="countries-marquee-item countries-marquee-item-city">Sherbrooke</li>
              <li class="countries-marquee-item countries-marquee-item-city">Peterborough</li>
              <li class="countries-marquee-item countries-marquee-item-city">Saint John</li>
              <li class="countries-marquee-item countries-marquee-item-city">Chilliwack</li>
              <li class="countries-marquee-item countries-marquee-item-city">Sarnia</li>
              <li class="countries-marquee-item countries-marquee-item-city">Chatham-Kent</li>
              <li class="countries-marquee-item countries-marquee-item-city">Prince George</li>
              <li class="countries-marquee-item countries-marquee-item-city">Medicine Hat</li>
              <li class="countries-marquee-item countries-marquee-item-city">Fredericton</li>
              <li class="countries-marquee-item countries-marquee-item-city">Brandon</li>
              <li class="countries-marquee-item countries-marquee-item-city">North Bay</li>
              <li class="countries-marquee-item countries-marquee-item-city">Prince Albert</li>
              <li class="countries-marquee-item countries-marquee-item-city">Cornwall</li>
              <li class="countries-marquee-item countries-marquee-item-city">Saint-Jérôme</li>
              <li class="countries-marquee-item countries-marquee-item-city">Belleville</li>
              <li class="countries-marquee-item countries-marquee-item-city">Airdrie</li>
              <li class="countries-marquee-item countries-marquee-item-city">Cape Breton–Sydney</li>
              <li class="countries-marquee-item countries-marquee-item-city">Leduc</li>
              <li class="countries-marquee-item countries-marquee-item-city">Vernon</li>
              <li class="countries-marquee-item countries-marquee-item-city">Woodstock</li>
              <li class="countries-marquee-item countries-marquee-item-city">Timmins</li>
              <li class="countries-marquee-item countries-marquee-item-city">Brockville</li>
              <li class="countries-marquee-item countries-marquee-item-city">Penticton</li>
              <li class="countries-marquee-item countries-marquee-item-city">Prince Rupert</li>
              <li class="countries-marquee-item countries-marquee-item-city">Lloydminster</li>
              <li class="countries-marquee-item countries-marquee-item-city">Moose Jaw</li>
              <li class="countries-marquee-item countries-marquee-item-city">Courtenay</li>
              <li class="countries-marquee-item countries-marquee-item-city">Parksville</li>
              <li class="countries-marquee-item countries-marquee-item-city">North Vancouver</li>
              <li class="countries-marquee-item countries-marquee-item-city">White Rock</li>
              <li class="countries-marquee-item countries-marquee-item-city">Campbell River</li>
              <li class="countries-marquee-item countries-marquee-item-city">Okotoks</li>
              <li class="countries-marquee-item countries-marquee-item-city">Innisfil</li>
              <li class="countries-marquee-item countries-marquee-item-city">Sault Ste. Marie</li>
              <li class="countries-marquee-item countries-marquee-item-city">Stratford</li>
              <li class="countries-marquee-item countries-marquee-item-city">Fort McMurray</li>
              <li class="countries-marquee-item countries-marquee-item-city">Salmon Arm</li>
              <li class="countries-marquee-item countries-marquee-item-city">Port Coquitlam</li>
              <li class="countries-marquee-item countries-marquee-item-city">New Westminster</li>
              <li class="countries-marquee-item countries-marquee-item-city">Langley</li>
              <li class="countries-marquee-item countries-marquee-item-city">Maple Ridge</li>
              <li class="countries-marquee-item countries-marquee-item-city">Coquitlam</li>
              <li class="countries-marquee-item countries-marquee-item-city">St. Thomas</li>
              <li class="countries-marquee-item countries-marquee-item-city">Wetaskiwin</li>
              <li class="countries-marquee-item countries-marquee-item-city">Sidney</li>
              <li class="countries-marquee-item countries-marquee-item-city">West Vancouver</li>
              <li class="countries-marquee-item countries-marquee-item-city">Cold Lake</li>
              <li class="countries-marquee-item countries-marquee-item-city">Fort Saskatchewan</li>
              <li class="countries-marquee-item countries-marquee-item-city">Dawson Creek</li>
              <li class="countries-marquee-item countries-marquee-item-city">Revelstoke</li>
              <li class="countries-marquee-item countries-marquee-item-city">Quesnel</li>
              <li class="countries-marquee-item countries-marquee-item-city">Terrace</li>
            </ul>
          </div>
        </div>

        <div class="countries-marquee" aria-label="Business categories and services">
          <div class="countries-marquee-track countries-marquee-track-left" data-marquee-track>
            <ul class="countries-marquee-group" role="list">
              <li class="countries-marquee-item countries-marquee-item-sector">Aggregates</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Lumber</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Concrete Products</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Windows and Doors</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Business Supplies</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Solar Products</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Lawn and Garden</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Building Products</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Rubber Products</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Fiberglass products</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Steel Building Kits</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Technology</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Fencing</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Home &amp; Leisure</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Made Local</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Appliances</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Electronics</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Lumber</li>
              <li class="countries-marquee-item countries-marquee-item-sector">General</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Automotive</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Tires</li>
              <li class="countries-marquee-item countries-marquee-item-sector">HVAC</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Real Estate</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Plumbing</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Construction and Renovation Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Cleaning Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Automotive Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Moving and Transportation Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Electrical Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Roofing Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Appliance Installation and Repair</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Water Systems and Well Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Security Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Pest Control and Extermination</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Interior and Exterior Design Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Event Planning and Catering</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Marketing and Advertising Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Health and Wellness Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Education and Training Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Retail</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Carpet and Flooring Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Tree and Arborist Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Fencing and Gate Installation</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Pool and Spa Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Glass Installation and Repair</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Grocery Stores and Food Supply Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Computer Repairs and IT Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Windows and Doors Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Landscaping Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Self-Storage Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Funeral Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Garden Centers</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Locksmiths &amp; Locks</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Hairdressers &amp; Beauty Salons</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Overhead &amp; Garage Doors</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Upholstery services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Pet Services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Gravel and Landscaping supplies</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Accountants</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Bookkeeping</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Business and corporate legal services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Business consulting and financial advice</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Chiropractors DC</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Courier Service</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Delivery services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Dentists</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Employment Agencies</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Estate planning</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Family law services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Financial Planning</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Laser Hair Removal</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Mortgage Brokers</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Optometrists</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Packaging company</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Physiotherapists</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Printing company</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Retirement planner</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Shipping company</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Tax preparation services</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Business tax preparation</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Tax services (For Individuals)</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Veterinarians</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Walk-in Clinics</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Waxing</li>
              <li class="countries-marquee-item countries-marquee-item-sector">Law Firm/Lawyers</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-jet-cream pb-12 sm:pb-14 lg:pb-16">
      <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
        <div class="gallery-track flex snap-x snap-mandatory gap-4 overflow-x-auto md:grid md:grid-cols-3 md:overflow-visible lg:gap-8">
          <article class="min-w-[84%] snap-start md:min-w-0">
            <img src="images/gallery-1.png" alt="Just Eat Takeaway team members" class="h-full w-full rounded-[30px] object-cover shadow-soft">
          </article>
          <article class="min-w-[84%] snap-start md:min-w-0">
            <img src="images/gallery-2.png" alt="Food and kitchen ingredients" class="h-full w-full rounded-[30px] object-cover shadow-soft">
          </article>
          <article class="min-w-[84%] snap-start md:min-w-0">
            <img src="images/gallery-3.jpg" alt="People standing together outdoors" class="h-full w-full rounded-[30px] object-cover shadow-soft">
          </article>
        </div>
      </div>
    </section>

    <section id="culture" class="bg-jet-cream pb-14 sm:pb-16 lg:pb-20">
      <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
        <div class="overflow-hidden rounded-[34px] bg-[#eceae7]">
          <div class="grid items-center gap-8 px-6 py-10 sm:px-8 md:grid-cols-[1.02fr_0.98fr] md:px-12 lg:px-16 lg:py-14">
            <div class="relative order-2 md:order-1">
              <img src="images/table-for-you.png" alt="Just Eat Takeaway paper bag" class="mx-auto w-full max-w-[520px] drop-shadow-[0_28px_28px_rgba(36,46,48,0.18)]">
            </div>

            <div class="order-1 md:order-2">
              <h2 class="jet-heading max-w-[11ch] text-[3rem] leading-[0.92] tracking-[-0.05em] text-jet-charcoal sm:text-[4rem] lg:text-[5rem]">
                what’s on the table for you
              </h2>
              <p class="mt-6 max-w-[32rem] text-lg font-semibold leading-8 text-jet-charcoal/90 sm:text-xl">
                Every day is a new opportunity. We move forward together, growing in our roles as we work to grow the business. We're free to build, proud to take responsibility, and excited to see our projects make a difference to millions of our customers and partners.
              </p>
              <a href="#" target="_blank" rel="noreferrer" class="mt-8 inline-flex rounded-full border border-jet-charcoal/55 bg-white px-7 py-4 text-lg font-semibold text-jet-charcoal transition hover:bg-jet-charcoal hover:text-white">
                Learn more about our culture
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="stories" class="bg-jet-cream pb-10 sm:pb-12 lg:pb-16">
      <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
        <div class="text-center">
          <h2 class="jet-heading text-[2.75rem] leading-[0.92] tracking-[-0.05em] sm:text-[4rem] lg:text-[5rem]">
            understand what we stand for
          </h2>
          <a href="#" target="_blank" rel="noreferrer" class="mt-5 inline-flex rounded-full border border-jet-charcoal/50 bg-white px-7 py-4 text-lg font-semibold text-jet-charcoal transition hover:bg-jet-charcoal hover:text-white">
            Watch more stories
          </a>
        </div>

        <div class="mt-8 lg:mt-10">
          <div data-carousel="stories" class="story-track flex snap-x snap-mandatory gap-6 overflow-x-auto lg:grid lg:grid-cols-3 lg:overflow-visible">
            <article class="group min-w-[84%] snap-start lg:min-w-0">
              <a href="https://www.youtube.com/watch?v=Wp2kA5oI69I" target="_blank" rel="noreferrer" class="block">
                <div class="relative overflow-hidden rounded-[28px] bg-white shadow-soft">
                  <img src="images/story-sven.png" alt="Meet Sven, our Chief HR Officer" class="aspect-[0.82] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                  <span class="play-button absolute left-1/2 top-1/2 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-jet-orange text-white shadow-[0_18px_30px_rgba(255,128,0,0.25)]">
                    <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M8 6.5v11l9-5.5L8 6.5Z"></path>
                    </svg>
                  </span>
                </div>
                <h3 class="mt-4 text-[1.9rem] font-black leading-tight tracking-[-0.04em] text-jet-charcoal lg:text-[2rem]">Meet Sven, our Chief HR Officer</h3>
              </a>
            </article>

            <article class="group min-w-[84%] snap-start lg:min-w-0">
              <a href="https://www.youtube.com/watch?v=MRShATwyDlU" target="_blank" rel="noreferrer" class="block">
                <div class="relative overflow-hidden rounded-[28px] bg-white shadow-soft">
                  <img src="images/story-jess.png" alt="Meet Jess, our Chief Product Officer" class="aspect-[0.82] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                  <span class="play-button absolute left-1/2 top-1/2 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-jet-orange text-white shadow-[0_18px_30px_rgba(255,128,0,0.25)]">
                    <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M8 6.5v11l9-5.5L8 6.5Z"></path>
                    </svg>
                  </span>
                </div>
                <h3 class="mt-4 text-[1.9rem] font-black leading-tight tracking-[-0.04em] text-jet-charcoal lg:text-[2rem]">Meet Jess, our Chief Product Officer</h3>
              </a>
            </article>

            <article class="group min-w-[84%] snap-start lg:min-w-0">
              <a href="https://www.youtube.com/watch?v=WpbPYeGlVjA" target="_blank" rel="noreferrer" class="block">
                <div class="relative overflow-hidden rounded-[28px] bg-white shadow-soft">
                  <img src="images/story-empower.png" alt="Empowering everyday convenience" class="aspect-[0.82] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                  <span class="play-button absolute left-1/2 top-1/2 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-jet-orange text-white shadow-[0_18px_30px_rgba(255,128,0,0.25)]">
                    <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M8 6.5v11l9-5.5L8 6.5Z"></path>
                    </svg>
                  </span>
                </div>
                <h3 class="mt-4 text-[1.9rem] font-black leading-tight tracking-[-0.04em] text-jet-charcoal lg:text-[2rem]">Empowering everyday convenience</h3>
              </a>
            </article>
          </div>

          <div class="mt-5 flex items-center justify-center gap-3 lg:hidden">
            <button type="button" data-carousel-prev="stories" class="flex h-10 w-10 items-center justify-center rounded-full bg-jet-orange text-white shadow-soft" aria-label="Previous story">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 18 9 12l6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </button>
            <span data-carousel-status="stories" class="text-sm font-bold uppercase tracking-[0.2em] text-jet-charcoal/60">1 / 3</span>
            <button type="button" data-carousel-next="stories" class="flex h-10 w-10 items-center justify-center rounded-full bg-jet-orange text-white shadow-soft" aria-label="Next story">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </section>

    <div class="bg-jet-cream pb-10 sm:pb-12">
      <div class="mx-auto flex max-w-[1440px] justify-center px-4 sm:px-6 lg:px-10">
        <span class="divider-mark" aria-hidden="true">
          <svg viewBox="0 0 32 32" fill="none">
            <path d="M16 3c-6.6 0-12 5.4-12 12c0 3.3 1.35 6.3 3.52 8.48L16 31l8.48-7.52A12 12 0 0 0 28 15C28 8.4 22.6 3 16 3Zm0 7a5 5 0 1 1 0 10a5 5 0 0 1 0-10Z" fill="currentColor"></path>
          </svg>
        </span>
      </div>
    </div>

    <section id="jet-at-a-glance" class="bg-jet-cream pb-14 sm:pb-16 lg:pb-20">
      <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
        <div class="overflow-hidden rounded-[34px] bg-[#c1dade]">
          <div class="grid items-center gap-8 px-6 py-8 sm:px-8 md:grid-cols-[0.95fr_1.05fr] lg:px-12 lg:py-10 xl:px-14">
            <div class="flex justify-center md:justify-start">
              <img src="images/at-a-glance.png" alt="Fries in a Just Eat Takeaway container" class="w-full max-w-[420px] drop-shadow-[0_22px_34px_rgba(36,46,48,0.18)]">
            </div>

            <div>
              <h2 class="jet-heading max-w-[11ch] text-[2.9rem] leading-[0.9] tracking-[-0.05em] text-jet-charcoal sm:text-[4rem] lg:text-[5.1rem]">
                Vopen Market
              </h2>

              <div class="mt-8 grid grid-cols-2 gap-x-7 gap-y-6 lg:grid-cols-3 lg:gap-x-10 lg:gap-y-8">
                <div>
                  <p class="text-[3rem] font-black leading-none tracking-[-0.05em] lg:text-[4rem]">356 <span class="text-[0.7em]">k</span></p>
                  <p class="mt-2 text-base font-semibold text-jet-charcoal/80 lg:text-lg">Partners</p>
                </div>
                <div>
                  <p class="text-[3rem] font-black leading-none tracking-[-0.05em] lg:text-[4rem]">61 <span class="text-[0.7em]">m</span></p>
                  <p class="mt-2 text-base font-semibold text-jet-charcoal/80 lg:text-lg">Active consumers</p>
                </div>
                <div>
                  <p class="text-[3rem] font-black leading-none tracking-[-0.05em] lg:text-[4rem]">653 <span class="text-[0.7em]">m</span></p>
                  <p class="mt-2 text-base font-semibold text-jet-charcoal/80 lg:text-lg">Orders</p>
                </div>
                <div>
                  <p class="text-[3rem] font-black leading-none tracking-[-0.05em] lg:text-[4rem]">€19</p>
                  <p class="mt-2 text-base font-semibold text-jet-charcoal/80 lg:text-lg">Gross transaction value</p>
                </div>
                <div>
                  <p class="text-[3rem] font-black leading-none tracking-[-0.05em] lg:text-[4rem]">16</p>
                  <p class="mt-2 text-base font-semibold text-jet-charcoal/80 lg:text-lg">Countries</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="explore-careers" class="bg-jet-cream pb-16 sm:pb-20 lg:pb-24">
      <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
        <h2 class="jet-heading career-explorer-title">
          Explore all careers
        </h2>

        <div data-carousel="career-pages" class="career-pages-track">
          <div class="career-pages-slide">
            <div class="career-explorer-grid">
              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M5 18h14M7.5 16v-3.5M12 16V10M16.5 16V6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    <path d="M8 8.5h5.5M13.5 8.5V3M13.5 3l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Sales</h3>
                <p class="career-explorer-card-copy">33 available jobs</p>
              </a>

              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="8" cy="7.5" r="2.75" stroke="currentColor" stroke-width="1.8"></circle>
                    <circle cx="16" cy="7.5" r="2.75" stroke="currentColor" stroke-width="1.8"></circle>
                    <path d="M4.75 18.5v-1.75c0-2.2 1.78-4 4-4h.5c1.2 0 2.3.54 3.05 1.38c.75-.84 1.85-1.38 3.05-1.38h.5c2.22 0 4 1.8 4 4v1.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Corporate</h3>
                <p class="career-explorer-card-copy">22 available jobs</p>
              </a>

              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <rect x="5" y="6" width="14" height="10" rx="1.6" stroke="currentColor" stroke-width="1.8"></rect>
                    <path d="M9 20h6M7 16.5h10l1 2.5H6l1-2.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="m10 10 1.4 1.4 3.6-3.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Tech &amp; Product</h3>
                <p class="career-explorer-card-copy">24 available jobs</p>
              </a>

              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="7.5" cy="16" r="3" stroke="currentColor" stroke-width="1.8"></circle>
                    <circle cx="17" cy="16" r="3" stroke="currentColor" stroke-width="1.8"></circle>
                    <path d="M10.5 16h3.6l-2.1-6h-2.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M13.8 10h3l2 6M12.5 8.5l2.5-2.5M5 10h2.5M4.5 7.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Operations &amp; Logistics</h3>
                <p class="career-explorer-card-copy">17 available jobs</p>
              </a>
            </div>
          </div>

          <div class="career-pages-slide">
            <div class="career-explorer-grid">
              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M5 13a7 7 0 1 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    <rect x="4.25" y="12" width="3.5" height="6" rx="1.5" stroke="currentColor" stroke-width="1.8"></rect>
                    <rect x="16.25" y="12" width="3.5" height="6" rx="1.5" stroke="currentColor" stroke-width="1.8"></rect>
                    <path d="M12 19.5h2.5a2 2 0 0 0 2-2V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Customer Service</h3>
                <p class="career-explorer-card-copy">12 available jobs</p>
              </a>

              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M5 18h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    <rect x="6" y="11.5" width="2.75" height="4.5" rx="0.8" stroke="currentColor" stroke-width="1.8"></rect>
                    <rect x="10.625" y="8.5" width="2.75" height="7.5" rx="0.8" stroke="currentColor" stroke-width="1.8"></rect>
                    <rect x="15.25" y="6" width="2.75" height="10" rx="0.8" stroke="currentColor" stroke-width="1.8"></rect>
                    <path d="M6.9 8.2 10 5.8l2.8 1.6 4.1-3.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Data &amp; Analytics</h3>
                <p class="career-explorer-card-copy">13 available jobs</p>
              </a>

              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M5 13.5V9.5c0-.5.4-.9.9-.9h2.4l5.7-2.9c.6-.3 1.3.1 1.3.8v10.9c0 .7-.7 1.1-1.3.8l-5.7-2.9H5.9c-.5 0-.9-.4-.9-.9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M9 15.2 10.4 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    <path d="M17.8 9.2c.9.7 1.4 1.7 1.4 2.8s-.5 2.1-1.4 2.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Marketing</h3>
                <p class="career-explorer-card-copy">7 available jobs</p>
              </a>

              <a href="#" target="_blank" rel="noreferrer" class="career-explorer-card">
                <span class="career-explorer-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <rect x="5" y="5" width="5" height="5" rx="1.1" stroke="currentColor" stroke-width="1.8"></rect>
                    <rect x="14" y="5" width="5" height="5" rx="1.1" stroke="currentColor" stroke-width="1.8"></rect>
                    <rect x="5" y="14" width="5" height="5" rx="1.1" stroke="currentColor" stroke-width="1.8"></rect>
                    <path d="M16.5 14v5M14 16.5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                  </svg>
                </span>
                <h3 class="jet-heading career-explorer-card-title">Other</h3>
                <p class="career-explorer-card-copy">5 available jobs</p>
              </a>
            </div>
          </div>
        </div>

        <div class="career-explorer-controls">
          <button type="button" data-carousel-prev="career-pages" class="career-explorer-button" aria-label="Previous career category page">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18 9 12l6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </button>
          <span data-carousel-status="career-pages" class="career-explorer-status">1 / 2</span>
          <button type="button" data-carousel-next="career-pages" class="career-explorer-button" aria-label="Next career category page">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </button>
        </div>
      </div>
    </section>

    <section class="mt-8 bg-jet-sand py-8 sm:mt-10 sm:py-9 lg:mt-12 lg:py-10">
      <div class="mx-auto flex max-w-[1440px] flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-10">
        <h2 class="jet-heading text-center text-[3rem] leading-none tracking-[-0.05em] lg:text-left lg:text-[4.2rem]">
          keep in touch
        </h2>

        <div class="flex flex-wrap items-center justify-center gap-3 lg:justify-end">
          <a href="https://www.linkedin.com/company/just-eat-takeaway-com/posts/?feedView=all" target="_blank" rel="noreferrer" class="social-pill">
            <span class="social-pill-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M6.94 8.5H3.56V20h3.38V8.5Zm.22-3.55C7.16 3.8 6.25 3 5.25 3s-1.9.8-1.9 1.95c0 1.12.88 1.94 1.85 1.94h.02c1.02 0 1.94-.82 1.94-1.94ZM20.44 13.04c0-3.44-1.83-5.04-4.27-5.04c-1.97 0-2.86 1.08-3.36 1.84V8.5H9.43c.05.89 0 11.5 0 11.5h3.38v-6.43c0-.34.03-.68.12-.92c.27-.67.88-1.37 1.91-1.37c1.35 0 1.9 1.03 1.9 2.54V20H20.1v-6.96h.34Z"></path>
              </svg>
            </span>
            Linkedin
          </a>

          <a href="https://www.instagram.com/wearejetcom/?hl=en" target="_blank" rel="noreferrer" class="social-pill">
            <span class="social-pill-icon">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <rect x="4.25" y="4.25" width="15.5" height="15.5" rx="4.25" stroke="currentColor" stroke-width="2"></rect>
                <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="2"></circle>
                <circle cx="17.35" cy="6.65" r="1.1" fill="currentColor"></circle>
              </svg>
            </span>
            Instagram
          </a>

          <a href="https://www.youtube.com/channel/UCsc8W65CYNODk27yrsctYMg" target="_blank" rel="noreferrer" class="social-pill">
            <span class="social-pill-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21.3 7.2a2.95 2.95 0 0 0-2.07-2.1C17.4 4.6 12 4.6 12 4.6s-5.4 0-7.23.5A2.95 2.95 0 0 0 2.7 7.2C2.2 9.06 2.2 12 2.2 12s0 2.94.5 4.8a2.95 2.95 0 0 0 2.07 2.1c1.83.5 7.23.5 7.23.5s5.4 0 7.23-.5a2.95 2.95 0 0 0 2.07-2.1c.5-1.86.5-4.8.5-4.8s0-2.94-.5-4.8ZM10.3 15.2V8.8L15.8 12l-5.5 3.2Z"></path>
              </svg>
            </span>
            Youtube
          </a>
        </div>
      </div>
    </section>

    <section class="bg-jet-charcoal">
      <div class="mx-auto grid max-w-[1440px] md:grid-cols-2">
        <a href="#" target="_blank" rel="noreferrer" class="utility-link border-b border-white/10 md:border-b-0 md:border-r md:border-white/10">
          Manage preferences
        </a>
        <a href="#" target="_blank" rel="noreferrer" class="utility-link">
          Personal Information
        </a>
      </div>
    </section>
    <?php include 'containerfooter.php'; ?>
