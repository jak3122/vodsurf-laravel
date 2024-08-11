import { useCallback, useEffect, useRef, useState } from "react";
import { Head } from "@inertiajs/react";
import useSettings from "@/store/useSettings";
import useVideoStore from "@/store/useVideoStore";
import useTimer from "@/hooks/useTimer";
import useStreamer from "@/hooks/useStreamer";

export default function Player() {
  const player = useRef(null);
  const [playerIsReady, setPlayerIsReady] = useState(false);
  const streamer = useStreamer();
  const settings = useSettings((state) => state.settings);
  const fetchVideos = useVideoStore((state) => state.fetchVideos);
  const video = useVideoStore((state) => state.videos[0]);
  const timer = useTimer();
  const onExpire = useCallback(
    () => fetchVideos({ streamer: streamer.route, settings }),
    [fetchVideos, streamer.route, settings]
  );
  timer.setOnExpire(onExpire);

  const autoplay = settings.autoplay || settings.mode === "endless";

  const setup = useCallback(() => {
    if (!window.YT) return;
    player.current = new window.YT.Player("yt-player", {
      videoId: video?.videoId,
      playerVars: {
        playsinline: 1,
        rel: 0,
        start: settings.randomStart ? video?.startSeconds : undefined,
      },
    });
    player.current.addEventListener("onReady", "onPlayerReady");
    player.current.addEventListener("onStateChange", "onPlayerStateChange");
  }, [video, settings.randomStart]);

  const onPlayerReady = useCallback(
    (event) => {
      setPlayerIsReady(true);
    },
    [setPlayerIsReady]
  );

  const onPlayerStateChange = useCallback(
    ({ data: state }) => {
      if (settings.mode !== "endless") return;
      console.log("state", state);
      switch (state) {
        case window.YT.PlayerState.UNSTARTED:
          timer.unstarted();
          break;
        case window.YT.PlayerState.ENDED:
          timer.pause();
          onExpire();
          break;
        case window.YT.PlayerState.PAUSED:
        case window.YT.PlayerState.BUFFERING:
          timer.pause();
          break;
        case window.YT.PlayerState.PLAYING:
          timerSettingsToSeconds(settings.timer) > 0 && timer.start();
          break;
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [settings.mode, timer, onExpire]
  );

  useEffect(() => {
    window.onPlayerReady = onPlayerReady;
    window.onPlayerStateChange = onPlayerStateChange;
  }, [onPlayerReady, onPlayerStateChange]);

  useEffect(() => {
    if (!player.current) setup();
    if (playerIsReady) {
      const options = {
        videoId: video?.videoId,
        startSeconds: settings.randomStart ? video?.startSeconds : undefined,
      };
      if (autoplay) {
        player.current.loadVideoById(options);
      } else {
        player.current.cueVideoById(options);
      }
    }
  }, [
    video?.videoId,
    playerIsReady,
    setPlayerIsReady,
    setup,
    video?.startSeconds,
    settings.randomStart,
    autoplay,
  ]);

  useEffect(() => {
    window.onYouTubeIframeAPIReady = () => {
      setup();
    };
  }, [setup]);

  return (
    <>
      <Head>
        <script id="yt-script" src="https://www.youtube.com/iframe_api" />
      </Head>
      <span
        style={{
          width: "100%",
          flex: "1",
        }}
      >
        <div id="yt-player" style={{ height: "100%", width: "100%" }} />
      </span>
    </>
  );
}

export function timerSettingsToSeconds({ h, m, s }) {
  const seconds = Number(h) * 60 * 60 + Number(m) * 60 + Number(s);
  return seconds;
}
