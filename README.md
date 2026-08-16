# MarkenFlux SMM Panel API Integration Guide

Welcome to the comprehensive developer documentation for the **MarkenFlux Social** API (`panel.markenflux.de`). This guide covers all available methods, request parameters, response structures, authentication requirements, and error-handling best practices to help you seamlessly integrate SMM services into your applications, platforms, or custom panels.

---

## Table of Contents

1. [API Base URL & Authentication](#api-base-url--authentication)
2. [Available Methods](#available-methods)
   * [1. Service List (`action=services`)](#1-service-list-actionservices)
   * [2. Create Order (`action=add`)](#2-create-order-actionadd)
   * [3. Order Status (`action=status`)](#3-order-status-actionstatus)
   * [4. Multiple Order Status (`action=status` with `orders`)](#4-multiple-order-status-actionstatus-with-orders)
   * [5. Create Refill (`action=refill`)](#5-create-refill-actionrefill)
   * [6. Multiple Refill (`action=refill` with `orders`)](#6-multiple-refill-actionrefill-with-orders)
   * [7. Account Balance (`action=balance`)](#7-account-balance-actionbalance)
   * [8. Cancel Order (`action=cancel`)](#8-cancel-order-actioncancel)
3. [Error Codes & Troubleshooting](#error-codes--troubleshooting)
4. [Code Examples](#code-examples)
   * [PHP cURL Example](#php-curl-example)
   * [Node.js Axios Example](#nodejs-axios-example)
   * [Python Requests Example](#python-requests-example)

---

## API Base URL & Authentication

All API requests must be sent via **HTTPS** to the following base endpoint:

```text
[https://panel.markenflux.de/api/v2](https://panel.markenflux.de/api/v2)
