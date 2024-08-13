import streamers from "@/streamers";

export default function useStreamer() {
  const path = window.location.pathname;
  const segments = path.split("/");
  const route = segments.find((segment) => segment !== "") || null;

  const streamer = streamers.find((streamer) =>
    streamer?.supportedRoutes?.includes(route)
  );

  return streamer;
}
