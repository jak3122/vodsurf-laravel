import Stats from "@/Components/Stats";
import Providers from "@/Layouts/Providers";
import { Flex } from "@chakra-ui/react";
import useStreamer from "@/hooks/useStreamer";
import Header from "@/Components/Header";
import Content from "@/Components/content";

export default function Streamer({ stats }) {
  const streamer = useStreamer();

  return (
    <div style={{ height: "100vh" }}>
      <Providers>
        <Flex flexDir="column" h="full" w="full" bg={streamer.theme.bg}>
          <Header />
          <Content>
            <Stats stats={stats} />
          </Content>
        </Flex>
      </Providers>
    </div>
  );
}
