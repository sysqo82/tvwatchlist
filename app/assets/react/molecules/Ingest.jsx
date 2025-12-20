import React from "react";
import Collapsible from 'react-collapsible';
import { useState, useEffect } from "react";
import ShowPoster from "../atoms/ShowPoster";
import IngestForm from "../organisms/IngestForm";

export default function Ingest() {
    const [showData, setShowData ] = useState(null);
    const [error, setError] = useState(null);
    const [searching, setSearching] = useState(false);
    const [inputValue, setInputValue] = useState('')
    const [timer, setTimer] = useState(0)

    const handleChange = event => {
        setInputValue(event.target.value)
        clearTimeout(timer)

        const newTimer = setTimeout(() => {
            if(event.target.value === '') return
            searchShows(event.target.value)
        }, 500)

        setTimer(newTimer)
    }
    function searchShows(string) {
        console.log("searching for " + string)
        setSearching(true);
        
        // Search both series and movies
        Promise.all([
            fetch(`/api/tvdb/search/series?seriesTitle=`+string, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json+ld"
                }
            }).then(r => r.json()),
            fetch(`/api/tvdb/search/movies?movieTitle=`+string, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json+ld"
                }
            }).then(r => r.json())
        ])
        .then(([seriesData, movieData]) => {
            console.log("Got series data:", seriesData);
            console.log("Got movie data:", movieData);
            
            // Combine results, adding a type field to distinguish
            const series = (seriesData.data || []).map(item => ({ ...item, type: 'series' }));
            const movies = (movieData.data || []).map(item => ({ ...item, type: 'movie' }));
            
            setShowData({
                status: 200,
                title: 'OK',
                data: [...series, ...movies]
            });
            setError(null);
        })
        .catch((err) => {
            setError(err.message);
            setShowData(null);
        })
        .finally(() => {
            setSearching(false);
        });
    }

    useEffect(() => {}, []);

    return (
        <div>
            {error && (
                <div>{`There is a problem fetching the post data - ${error}`}</div>
            )}
            <div className={"bento"}>
                <h1>Show to search</h1>
                <input value={inputValue} name="search" onChange={handleChange} placeholder="Search for a TV show or movie"/>
            </div>
            {searching &&
                <div className={"searching bento"}>Searching...</div>
            }
            {showData && showData.data.map((show) => (
                <div key={show.tvdbId} className="ingestCard bento">
                    <div className="d-flex justify-content-between align-items-start mb-2">
                        <h3 className="mb-0">{show.title}</h3>
                        <span className={`badge ${show.type === 'movie' ? 'bg-info' : 'bg-primary'}`}>
                            {show.type === 'movie' ? 'Movie' : 'Series'}
                        </span>
                    </div>
                    <ShowPoster
                        image={show.poster}
                        title={show.title}
                    />
                    <p>{show.overview}</p>
                    <Collapsible trigger={`Add this ${show.type === 'movie' ? 'movie' : 'show'}`}>
                        <IngestForm
                            id={show.tvdbId}
                            type={show.type}
                        />
                    </Collapsible>
                </div>
            ))}
            <div>

            </div>
        </div>
    )
}
