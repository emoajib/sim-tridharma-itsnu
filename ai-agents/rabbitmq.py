import json
import logging
import time

import pika

from config import RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASSWORD, RABBITMQ_EXCHANGE, RABBITMQ_QUEUE

logger = logging.getLogger("rabbitmq")


class RabbitMQConsumer:
    def __init__(self):
        self.connection = None
        self.channel = None
        self._running = False

    def connect(self):
        credentials = pika.PlainCredentials(RABBITMQ_USER, RABBITMQ_PASSWORD)
        parameters = pika.ConnectionParameters(
            host=RABBITMQ_HOST,
            port=RABBITMQ_PORT,
            credentials=credentials,
            heartbeat=600,
            blocked_connection_timeout=300,
        )
        self.connection = pika.BlockingConnection(parameters)
        self.channel = self.connection.channel()
        self.channel.exchange_declare(exchange=RABBITMQ_EXCHANGE, exchange_type="direct", durable=True)
        self.channel.queue_declare(queue=RABBITMQ_QUEUE, durable=True)
        self.channel.queue_bind(queue=RABBITMQ_QUEUE, exchange=RABBITMQ_EXCHANGE, routing_key=RABBITMQ_QUEUE)
        self.channel.basic_qos(prefetch_count=1)
        logger.info("Connected to RabbitMQ")

    def process_message(self, body: dict):
        agent_name = body.get("agent")
        data = body.get("data", {})
        task_id = body.get("task_id")

        if not agent_name:
            logger.error("No agent field in message")
            return {"error": "No agent specified"}

        from agents import get_agent
        agent = get_agent(agent_name)
        if agent is None:
            logger.error(f"Unknown agent: {agent_name}")
            return {"error": f"Agent '{agent_name}' not found"}

        logger.info(f"Routing to agent '{agent_name}' (task_id={task_id})")
        return agent.execute(data)

    def on_message(self, ch, method, properties, body):
        try:
            message = json.loads(body)
            logger.info(f"Received message: {message.get('agent')}")
            self.process_message(message)
            ch.basic_ack(delivery_tag=method.delivery_tag)
        except Exception as e:
            logger.error(f"Error processing message: {e}", exc_info=True)
            ch.basic_nack(delivery_tag=method.delivery_tag, requeue=False)

    def start_consuming(self):
        self._running = True
        while self._running:
            try:
                self.connect()
                self.channel.basic_consume(queue=RABBITMQ_QUEUE, on_message_callback=self.on_message)
                self.channel.start_consuming()
            except pika.exceptions.ConnectionClosedByBroker:
                logger.warning("Connection closed by broker, reconnecting...")
                time.sleep(5)
            except pika.exceptions.AMQPConnectionError:
                logger.warning("Connection lost, reconnecting...")
                time.sleep(5)

    def stop(self):
        self._running = False
        if self.channel and self.channel.is_open:
            self.channel.stop_consuming()
        if self.connection and self.connection.is_open:
            self.connection.close()
        logger.info("RabbitMQ consumer stopped")


class RabbitMQPublisher:
    def __init__(self):
        self.connection = None
        self.channel = None

    def connect(self):
        credentials = pika.PlainCredentials(RABBITMQ_USER, RABBITMQ_PASSWORD)
        parameters = pika.ConnectionParameters(
            host=RABBITMQ_HOST,
            port=RABBITMQ_PORT,
            credentials=credentials,
        )
        self.connection = pika.BlockingConnection(parameters)
        self.channel = self.connection.channel()
        self.channel.exchange_declare(exchange=RABBITMQ_EXCHANGE, exchange_type="direct", durable=True)
        logger.info("RabbitMQ publisher connected")

    def publish(self, routing_key: str, message: dict):
        if not self.channel or self.channel.is_closed:
            self.connect()
        self.channel.basic_publish(
            exchange=RABBITMQ_EXCHANGE,
            routing_key=routing_key,
            body=json.dumps(message),
            properties=pika.BasicProperties(delivery_mode=2),
        )

    def close(self):
        if self.connection and self.connection.is_open:
            self.connection.close()
