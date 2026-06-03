export class ApiError extends Error {
  constructor (
    message: string,
    public readonly status: number,
    public readonly response: Response
  ) {
    super(message)
    this.name = 'ApiError'
  }
}
