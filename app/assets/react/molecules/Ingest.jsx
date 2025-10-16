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
        fetch(`/api/tvdb/search/series?seriesTitle=`+string, {
            method: "GET",
            headers: {
                "Content-Type": "application/json+ld"
            }
        })
        .then((response) => {
            if(!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((showData) => {
            console.log("Got show data " + showData);
            setShowData(showData);
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
                <input value={inputValue} name="search" onChange={handleChange} placeholder="Search for a tv show"/>
            </div>
            {searching &&
                <div className={"searching bento"}>Searching...</div>
            }
            {showData && showData.data.map((show) => (
                <div key={show.tvdbId} className="ingestCard bento">
                    <h3>{show.title}</h3>
                    <ShowPoster
                        image={show.poster}
                        title={show.title}
                    />
                    <p>{show.overview}</p>
                    <Collapsible trigger="Add this show">
                        <IngestForm
                            id={show.tvdbId}
                        />
                    </Collapsible>
                </div>
            ))}
            <div>

            </div>
        </div>
    )
}
