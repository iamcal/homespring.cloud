// Minimal driver around Addison Bean's 2017 Rust crate so the harness can
// run it against arbitrary source files.
//
// The crate itself ships only a hardcoded demo in its own main.rs and has
// `Program::execute()` as `unimplemented!()` for River programs. This
// driver fills that gap: parse via Node::parse_program, then run a tick
// loop in the usual Homespring phase order. We skip Tick::Power and
// Tick::Input because their PropagationOrder is `Any`, which hits
// `unimplemented!()` inside Node::tick and would panic immediately — the
// rest of the ticks (Snow, Water, Misc, FishHatch, FishDown, FishUp) are
// all actually wired up and can produce real behavior for a subset of
// programs (notably the null program, which Program::Quine::execute
// hardcodes the correct output for).
//
// Env vars, matching the other drivers in tests/patches/:
//   HS_LIMIT=N   cap tick count (default 200)
//   HS_TICKS=1   emit "TICKS:<n>" on stderr at exit

extern crate homespring;

use homespring::river::Node;
use homespring::Program;
use homespring::Tick;

use std::env;
use std::fs;
use std::process;

fn main() {
    let args: Vec<String> = env::args().collect();
    if args.len() < 2 {
        eprintln!("usage: {} <file>", args[0]);
        process::exit(2);
    }
    let source = match fs::read_to_string(&args[1]) {
        Ok(s) => s,
        Err(e) => {
            eprintln!("read error: {}", e);
            process::exit(2);
        }
    };

    let limit: usize = env::var("HS_LIMIT")
        .ok()
        .and_then(|s| s.parse().ok())
        .unwrap_or(200);
    let emit_ticks = env::var("HS_TICKS").is_ok();

    let program = Node::parse_program(&source);

    let mut actual_ticks: usize = 0;
    match program {
        Program::Quine => {
            let mut q = Program::Quine;
            q.execute();
        }
        Program::River(root) => {
            let phases = [
                Tick::Snow,
                Tick::Water,
                Tick::Misc,
                Tick::FishHatch,
                Tick::FishDown,
                Tick::FishUp,
            ];
            for i in 0..limit {
                actual_ticks = i + 1;
                for &t in &phases {
                    root.borrow_mut().tick(t);
                }
            }
        }
    }

    if emit_ticks {
        eprintln!("TICKS:{}", actual_ticks);
    }
}
