# Code Quality

This project uses StandardJS coding standards for JavaScript/TypeScript code quality.

## Type Safety

- You must use TypeScript to ensure type safety

## Component Structure

- Reusable components must be their own modular files

## Vue API Usage

- Use the Composition API, never the Options API
- Use `<script setup>` over `setup()` to avoid unnecessary boilerplate

## Script Organization

- Group reactive state, computed properties, and methods that belong to the same feature next to each other
- Avoid grouping all `ref` elements at the top and all methods at the bottom of script blocks
- Prefer feature-grouped reactive blocks and composable function setups