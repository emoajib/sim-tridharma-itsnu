import subprocess
import sys


def main():
    cmd = [
        sys.executable, "-m", "celery",
        "-A", "worker.celery_app",
        "worker",
        "--loglevel=info",
        "--concurrency=4",
    ]
    subprocess.run(cmd)


if __name__ == "__main__":
    main()
