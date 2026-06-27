<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Book Wizard - CineTime</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #0a0a0f; 
            color: white; 
            font-family: 'Open Sans', sans-serif;
            overflow: hidden; /* Lock master scroll for full screen slides */
        }
        
        .wizard-track {
            display: flex;
            width: 100vw;
            height: 100vh;
            transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .slide {
            min-width: 100vw;
            height: 100vh;
            padding: 80px 5%;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .slide-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 2rem;
            text-shadow: 0 4px 15px rgba(0,0,0,0.5);
            text-align: center;
        }
        .slide-title span { color: #f84464; }

        /* Slide 1: Cities */
        .city-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .city-card:hover { 
            background: rgba(248, 68, 100, 0.2); 
            border-color: #f84464; 
            transform: translateY(-5px); 
        }
        .city-card i { font-size: 2rem; margin-bottom: 15px; color: #f84464; }

        /* Slide 2: Movies */
        .movie-card {
            background: #1c1c1c;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: 0.3s;
            border: 2px solid transparent;
            height: 100%;
        }
        .movie-card:hover {
            transform: scale(1.05);
            border-color: #f84464;
            box-shadow: 0 10px 30px rgba(248, 68, 100, 0.4);
        }
        .movie-card img { width: 100%; height: 300px; object-fit: cover; }
        .movie-info { padding: 15px; text-align: center; }

        /* Slide 3: Dates */
        .date-chip {
            background: #1e1e28;
            border: 2px solid #333;
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
        }
        .date-chip:hover {
            border-color: #f84464;
            background: rgba(248, 68, 100, 0.1);
        }

        /* Nav Buttons */
        .back-btn {
            position: absolute;
            top: 30px;
            left: 5%;
            font-size: 1.2rem;
            color: #888;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 1000;
            transition: 0.3s;
        }
        .back-btn:hover { color: white; }

        .close-btn {
            position: absolute;
            top: 30px;
            right: 5%;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
            z-index: 1000;
        }
        .close-btn:hover { color: #f84464; }

        /* Final Stub */
        .ticket-stub {
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.02));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        
        .loader { display: none; margin: 50px auto; color: #f84464; }
    </style>
</head>
<body>

    <button id="backBtn" class="back-btn d-none" onclick="goBack()"><i class="fas fa-arrow-left me-2"></i> Back</button>
    <a href="index.php" class="close-btn"><i class="fas fa-times"></i></a>

    <div class="wizard-track" id="track">
        
        <!-- SLIDE 1: CITY -->
        <div class="slide" id="s1">
            <h1 class="slide-title">Where are you <span>Watching?</span></h1>
            
            <div id="loader1" class="loader spinner-border"></div>
            
            <div class="container">
                <div class="row justify-content-center g-4" id="cityContainer">
                    <!-- Dynamic Cities -->
                </div>
            </div>
        </div>

        <!-- SLIDE 2: MOVIE -->
        <div class="slide" id="s2">
            <h1 class="slide-title">Select your <span>Movie</span> in <span id="lblCity"></span></h1>
            
            <div id="loader2" class="loader spinner-border"></div>
            
            <div class="container">
                <div class="row justify-content-center g-4" id="movieContainer">
                    <!-- Dynamic Movies -->
                </div>
            </div>
        </div>

        <!-- SLIDE 3: DATE -->
        <div class="slide" id="s3">
            <h1 class="slide-title">When are you <span>Going?</span></h1>
            <p class="text-secondary text-center mb-5"><i class="fas fa-film text-danger"></i> <span id="lblMovie"></span> &bull; <i class="fas fa-map-marker-alt text-info"></i> <span id="lblCity2"></span></p>

            <div id="loader3" class="loader spinner-border"></div>
            
            <div class="container">
                <div class="row justify-content-center g-4" id="dateContainer">
                    <!-- Dynamic Dates -->
                </div>
            </div>
        </div>

        <!-- SLIDE 4: CONFIRMATION -->
        <div class="slide" id="s4">
            <div class="ticket-stub mt-5">
                <h2 class="text-success fw-bold mb-4"><i class="fas fa-check-circle me-2"></i> Awesome Choice!</h2>
                
                <h3 class="fw-bold fs-2" id="confMovie">Movie Name</h3>
                <p class="text-warning fs-5"><i class="fas fa-calendar-alt"></i> <span id="confDate">Date</span></p>
                <div class="badge bg-secondary fs-6 px-3 py-2 mb-5"><i class="fas fa-map-marker-alt text-danger me-1"></i> <span id="confCity">City</span></div>
                
                <form action="booking.php" method="GET">
                    <input type="hidden" name="movie_id" id="finalMovieId">
                    <input type="hidden" name="city" id="finalCity">
                    <input type="hidden" name="date" id="finalDate">
                    
                    <button type="submit" class="btn btn-lg w-100 fw-bold" style="background:#f84464; color:white; border-radius:50px;">
                        Continue to Showtimes <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

    </div>

<script>
    let currentSlideIndex = 0;
    
    // Application State
    let BookingState = {
        city: '',
        movie_id: '',
        movie_title: '',
        raw_date: '',
        display_date: ''
    };

    function moveSlide(index) {
        currentSlideIndex = index;
        document.getElementById('track').style.transform = `translateX(-${index * 100}vw)`;
        
        // Handle Back button visibility
        document.getElementById('backBtn').classList.toggle('d-none', index === 0);
    }
    
    function goBack() {
        if(currentSlideIndex > 0) moveSlide(currentSlideIndex - 1);
    }

    // Slide 1 Init
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById('loader1').style.display = 'block';
        fetch('api_wizard.php?action=cities')
            .then(res => res.json())
            .then(data => {
                document.getElementById('loader1').style.display = 'none';
                const cont = document.getElementById('cityContainer');
                if(data.success && data.data.length > 0) {
                    data.data.forEach(c => {
                        const div = document.createElement('div');
                        div.className = 'col-md-3 col-sm-6';
                        div.innerHTML = `
                            <div class="city-card" onclick="selectCity('${c}')">
                                <i class="fas fa-city"></i>
                                <h4 class="m-0">${c}</h4>
                            </div>
                        `;
                        cont.appendChild(div);
                    });
                } else {
                    cont.innerHTML = '<div class="alert alert-danger col-12 text-center border-0 bg-dark text-warning">No active cities hosting movies currently found.</div>';
                }
            });
    });

    // Handle City Click
    function selectCity(city) {
        BookingState.city = city;
        document.getElementById('lblCity').innerText = city;
        document.getElementById('lblCity2').innerText = city;
        
        // Reset and Load Slide 2
        document.getElementById('movieContainer').innerHTML = '';
        document.getElementById('loader2').style.display = 'block';
        
        fetch(`api_wizard.php?action=movies&city=${encodeURIComponent(city)}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('loader2').style.display = 'none';
                const cont = document.getElementById('movieContainer');
                if(data.success && data.data.length > 0) {
                    data.data.forEach(m => {
                        const star = m.rating !== null ? `<span class="text-warning fw-bold"><i class="fas fa-star"></i> ${parseFloat(m.rating).toFixed(1)}/10</span>` : `<span class="text-secondary small">Not Rated</span>`;
                        
                        const div = document.createElement('div');
                        div.className = 'col-xl-3 col-lg-4 col-md-6';
                        div.innerHTML = `
                            <div class="movie-card" onclick="selectMovie(${m.movie_id}, '${m.title.replace(/'/g, "\\'")}')">
                                <img src="${m.poster_url}" alt="${m.title}">
                                <div class="movie-info">
                                    <h5 class="fw-bold mb-1">${m.title}</h5>
                                    <div>${star}</div>
                                </div>
                            </div>
                        `;
                        cont.appendChild(div);
                    });
                }
            });
            
        moveSlide(1);
    }

    // Handle Movie Click
    function selectMovie(id, title) {
        BookingState.movie_id = id;
        BookingState.movie_title = title;
        document.getElementById('lblMovie').innerText = title;

        // Reset and Load Slide 3
        document.getElementById('dateContainer').innerHTML = '';
        document.getElementById('loader3').style.display = 'block';
        
        fetch(`api_wizard.php?action=dates&movie_id=${id}&city=${encodeURIComponent(BookingState.city)}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('loader3').style.display = 'none';
                const cont = document.getElementById('dateContainer');
                if(data.success && data.data.length > 0) {
                    data.data.forEach(d => {
                        const div = document.createElement('div');
                        div.className = 'col-md-4 col-sm-6';
                        div.innerHTML = `
                            <div class="date-chip" onclick="selectDate('${d.raw_date}', '${d.display_date}')">
                                ${d.display_date}
                            </div>
                        `;
                        cont.appendChild(div);
                    });
                } else {
                    cont.innerHTML = '<div class="alert alert-secondary col-12 text-center text-white bg-dark">No upcoming timings configured natively for this selection yet!</div>';
                }
            });

        moveSlide(2);
    }

    // Handle Date Click
    function selectDate(raw_date, display_date) {
        BookingState.raw_date = raw_date;
        BookingState.display_date = display_date;
        
        // Populate Slide 4
        document.getElementById('confMovie').innerText = BookingState.movie_title;
        document.getElementById('confCity').innerText = BookingState.city;
        document.getElementById('confDate').innerText = BookingState.display_date;
        
        document.getElementById('finalMovieId').value = BookingState.movie_id;
        document.getElementById('finalCity').value = BookingState.city;
        document.getElementById('finalDate').value = BookingState.raw_date;

        moveSlide(3);
    }

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
