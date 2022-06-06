FROM rabbitmq:management
RUN rabbitmq-plugins enable rabbitmq_stomp
RUN rabbitmq-plugins enable rabbitmq_web_stomp
RUN rabbitmq-plugins enable rabbitmq_web_stomp_examples
# COPY rabbitmq.conf /etc/rabbitmq/rabbitmq.conf


EXPOSE 61613
# ws://127.0.0.1:15674/ws
EXPOSE 15674
# http://127.0.0.1:15670/
EXPOSE 15670
# http://127.0.0.1:15672/
EXPOSE 15672
