import React from 'react'

export default function WatchedButton({id, refreshState, size = "lg", className = ""}) {
    const handleClick = () => {
        const watchedEpisode = fetch('/api/episodes/' + id, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json"
            },
            body: JSON.stringify({
                watched: true
            })
        })
        .then((response) => {
            if (!response.ok) {
                console.error(`Failed to mark episode ${id} as watched:`, response.status, response.statusText);
                throw new Error(`Failed to mark episode as watched: ${response.status}`);
            }
            return response.json();
        })
        .catch((error) => {
            console.error(`Error marking episode ${id} as watched:`, error);
            throw error;
        });

        watchedEpisode.then(episode => {
            let date = new Date().toUTCString();
            return fetch('/api/history', {
                method: "POST",
                headers: {
                    "Content-Type": "application/ld+json"
                },
                body: JSON.stringify({
                    seriesTitle: episode.seriesTitle,
                    tvdbSeriesId: episode.tvdbSeriesId,
                    episodeTitle: episode.title,
                    episodeDescription: episode.description,
                    season: episode.season,
                    episode: episode.episode,
                    episodeId: episode.id.toString(),
                    airDate: episode.airDate,
                    poster: episode.poster,
                    watchedAt: date
                })
            })
            .then((response) => {
                if (!response.ok) {
                    console.error(`Failed to create History for episode ${episode.id}:`, response.status, response.statusText);
                    throw new Error(`Failed to create History entry: ${response.status}`);
                }
                return response.json();
            });
        })
        .catch((error) => {
            console.error(`Critical error in WatchedButton for episode ${id}:`, error);
            alert(`Failed to mark episode as watched. Please try again. Error: ${error.message}`);
        })
        .finally(() => {
            refreshState();
        });
    };

    return (
        <div className="component text-center" id="watched">
            <button 
                className={`btn btn-${size} ${size === 'lg' ? 'btn-block' : ''} btn-success ${className}`}
                type="button" 
                onClick={handleClick}
            >
                Watched
            </button>
        </div>
    )
}
