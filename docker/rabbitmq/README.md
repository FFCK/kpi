docker run -it -v $(pwd)/rabbitmq.conf:/etc/rabbitmq/rabbitmq.conf -p 61613:61613 -p 15674:15674 -p 15670:15670 -p 15672:15672 rabbitmq:Dockerfile

