var rentalMoviesApp = angular.module("rentalMovies", []);

rentalMoviesApp.controller("MovieCtrl", function($scope, $http, $q) {
    $scope.movies = {};
    $scope.query = {"json" : 1};

    var fetchRequest = null;
    $scope.fetchMovies = function() {
        if(fetchRequest) { fetchRequest.resolve(); }
        fetchRequest = $q.defer();
        
        $http({
            method: 'GET',
            url: "browse.php",
            params: $scope.query,
        }).success(function(data) {
            $("#genreList").fadeOut();
            $("#latestMovieList").fadeOut();
            $("#newsList").fadeOut();
            $scope.movies = data;
        });
    };

    $scope.query.ob = "title";
    $scope.query.o = "ASC";

    $scope.fetchMovies();

});

$(function() {
    $("#query").on("keyup change", function() {
        if($(this).val().length > 0) {
            $("#big-search").animate({height: "200px"}, 500);
            $("#big-search-bg").animate({height: "200px"}, 500);
            $("#big-search-box").animate({height: "200px"}, 500);
            $("#register").fadeOut();
            $("#genreList").fadeOut();
            $("#latestMovieList").fadeOut();
            $("#newsList").fadeOut();
        }
        else {
            $("#big-search").animate({height: "600px"}, 500);
            $("#big-search-bg").animate({height: "600px"}, 500);
            $("#big-search-box").animate({height: "600px"}, 500);
            $("#register").fadeIn();
            $("#genreList").fadeIn();
            $("#latestMovieList").fadeIn();
            $("#newsList").fadeIn();
        }
    });
});