import React, { useState, useEffect } from 'react';

export default function Archive() {
    const [archivedSeries, setArchivedSeries] = useState([]);
    const [archivedMovies, setArchivedMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [seriesNetworks, setSeriesNetworks] = useState({});

    function fetchArchivedSeries() {
        setLoading(true);
        
        fetch('/api/archive', {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                const series = data.archivedSeries || [];
                const movies = data.archivedMovies || [];
                setArchivedSeries(series);
                setArchivedMovies(movies);
                setError(null);
                
                // Fetch network information for each series
                series.forEach(seriesItem => {
                    if (seriesItem.tvdbSeriesId) {
                        fetchSeriesNetwork(seriesItem.tvdbSeriesId);
                    }
                });
            })
            .catch((err) => {
                setError(err.message);
                setArchivedSeries([]);
                setArchivedMovies([]);
            })
            .finally(() => {
                setLoading(false);
            });
    }

    function fetchSeriesNetwork(tvdbSeriesId) {
        fetch(`/api/series/${tvdbSeriesId}/network`)
            .then(response => response.json())
            .then(data => {
                setSeriesNetworks(prev => ({
                    ...prev,
                    [tvdbSeriesId]: data.network
                }));
            })
            .catch(() => {
                // Ignore network fetch errors
            });
    }

    function restoreSeries(tvdbSeriesId, seriesTitle) {
        if (!window.confirm(`Are you sure you want to restore "${seriesTitle}" to your watchlist?`)) {
            return;
        }

        fetch(`/api/archive/${tvdbSeriesId}/restore`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to restore series");
                }
                return response.json();
            })
            .then(() => {
                // Remove from archived list
                setArchivedSeries(prev => prev.filter(series => series.tvdbSeriesId !== tvdbSeriesId));
            })
            .catch((err) => {
                alert(`Error restoring series: ${err.message}`);
            });
    }

    function permanentlyDeleteSeries(tvdbSeriesId, seriesTitle) {
        if (!window.confirm(`Are you sure you want to PERMANENTLY delete "${seriesTitle}"? This action cannot be undone.`)) {
            return;
        }

        fetch(`/api/archive/${tvdbSeriesId}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to delete series");
                }
                return response.json();
            })
            .then(() => {
                // Remove from archived list
                setArchivedSeries(prev => prev.filter(series => series.tvdbSeriesId !== tvdbSeriesId));
            })
            .catch((err) => {
                alert(`Error deleting series: ${err.message}`);
            });
    }

    function restoreMovie(tvdbMovieId, movieTitle) {
        if (!window.confirm(`Are you sure you want to restore "${movieTitle}" to your watchlist?`)) {
            return;
        }

        fetch(`/api/archive/movies/${tvdbMovieId}/restore`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to restore movie");
                }
                return response.json();
            })
            .then(() => {
                setArchivedMovies(prev => prev.filter(movie => movie.tvdbMovieId !== tvdbMovieId));
            })
            .catch((err) => {
                alert(`Error restoring movie: ${err.message}`);
            });
    }

    function permanentlyDeleteMovie(tvdbMovieId, movieTitle) {
        if (!window.confirm(`Are you sure you want to PERMANENTLY delete "${movieTitle}"? This action cannot be undone.`)) {
            return;
        }

        fetch(`/api/archive/movies/${tvdbMovieId}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to delete movie");
                }
                return response.json();
            })
            .then(() => {
                setArchivedMovies(prev => prev.filter(movie => movie.tvdbMovieId !== tvdbMovieId));
            })
            .catch((err) => {
                alert(`Error deleting movie: ${err.message}`);
            });
    }

    useEffect(() => {
        fetchArchivedSeries();
    }, []);

    return (
        <div>
            {loading && (
                <div className="bento">
                    <div>Loading archived shows...</div>
                </div>
            )}

            {error && (
                <div className="bento">
                    <div className="text-danger">Error loading archived shows: {error}</div>
                </div>
            )}

            {!loading && archivedSeries.length === 0 && archivedMovies.length === 0 && (
                <div className="bento text-center">
                    <h1 id="nothing-found">No Archived Content</h1>
                    <p>Shows and movies you remove from your watchlist will appear here.</p>
                </div>
            )}

            {archivedMovies.length > 0 && (
                <>
                    <div className="bento mb-3">
                        <h2 className="text-light mb-3">Archived Movies</h2>
                    </div>
                    {archivedMovies.map((movie) => (
                        <div key={movie.tvdbMovieId} className="bento mb-3 series-group-archive">
                            <div className="d-flex align-items-start gap-3">
                                <div className="flex-shrink-0">
                                    <img 
                                        src={movie.poster} 
                                        alt={movie.title}
                                        className="img-fluid movie-poster"
                                        onError={(e) => {
                                            e.target.onerror = null;
                                            e.target.src = '/build/images/fallback-image.png';
                                        }}
                                    />
                                </div>
                                <div className="flex-grow-1">
                                    <div className="d-flex justify-content-between align-items-start mb-2">
                                        <div className="flex-grow-1">
                                            <h3 className="mb-1 text-light">{movie.title}</h3>
                                            <div className="mb-2">
                                                <small className="text-light me-3">
                                                    📅 Archived: {new Date(movie.archivedAt).toLocaleDateString('en-GB')}
                                                </small>
                                            </div>
                                            <div className="mb-2">
                                                {movie.watched && (
                                                    <span className="badge bg-success me-2">Watched</span>
                                                )}
                                            </div>
                                        </div>
                                        <div className="d-flex flex-column gap-2">
                                            <button 
                                                className="btn btn-sm btn-success"
                                                onClick={() => restoreMovie(movie.tvdbMovieId, movie.title)}
                                            >
                                                🔄 Restore
                                            </button>
                                            <button 
                                                className="btn btn-sm btn-outline-danger"
                                                onClick={() => permanentlyDeleteMovie(movie.tvdbMovieId, movie.title)}
                                            >
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </div>
                                    {movie.description && (
                                        <p className="text-light mb-0 small">{movie.description}</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </>
            )}

            {archivedSeries.length > 0 && (
                <>
                    <div className="bento mb-3">
                        <h2 className="text-light mb-3">Archived TV Shows</h2>
                    </div>
                    {archivedSeries.map((series) => (
                        <div key={series.tvdbSeriesId} className="bento mb-3 series-group-archive">
                            {/* Series Header */}
                            <div className="d-flex align-items-start gap-3 mb-3">
                                {/* Series Poster */}
                                <div className="flex-shrink-0">
                                    <img 
                                        src={series.poster} 
                                        alt={series.seriesTitle}
                                        className="img-fluid series-poster"
                                        onError={(e) => {
                                            e.target.onerror = null; // Prevent infinite loop
                                            e.target.src = '/build/images/fallback-image.png';
                                        }}
                                    />
                                </div>
                                
                                {/* Series Info */}
                                <div className="flex-grow-1">
                                    <div className="d-flex justify-content-between align-items-start mb-2">
                                        <div className="flex-grow-1">
                                            <h3 className="mb-1 text-light">{series.seriesTitle}</h3>
                                            
                                            <div className="mb-2">
                                                <small className="text-light me-3">
                                                    📅 Archived: {new Date(series.archivedAt).toLocaleDateString()}
                                                </small>
                                            </div>

                                            <div className="mb-2">
                                                {seriesNetworks[series.tvdbSeriesId] && (
                                                    <span className="badge bg-primary me-2">{seriesNetworks[series.tvdbSeriesId]}</span>
                                                )}
                                            </div>

                                            <div className="mb-2">
                                                <small className="text-light">
                                                    Progress: {series.watchedEpisodes}/{series.totalEpisodes} episodes watched
                                                </small>
                                                <div className="progress mt-1">
                                                    <div 
                                                        className="progress-bar bg-success" 
                                                        role="progressbar" 
                                                        style={{width: `${series.totalEpisodes > 0 ? (series.watchedEpisodes / series.totalEpisodes) * 100 : 0}%`}}
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="d-flex flex-column gap-2">
                                            <button 
                                                className="btn btn-sm btn-success"
                                                onClick={() => restoreSeries(series.tvdbSeriesId, series.seriesTitle)}
                                            >
                                                🔄 Restore
                                            </button>
                                            <button 
                                                className="btn btn-sm btn-outline-danger"
                                                onClick={() => permanentlyDeleteSeries(series.tvdbSeriesId, series.seriesTitle)}
                                            >
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {series.overview && (
                                        <p className="text-light mb-0 small">{series.overview}</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </>
            )}
        </div>
    );
}